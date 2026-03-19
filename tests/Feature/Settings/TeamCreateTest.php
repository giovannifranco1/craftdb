<?php

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var \Tests\TestCase $this */
    $this->user = User::factory()->create();

    // Create initial team for the user
    $team = Team::create([
        'user_id' => $this->user->id,
        'name' => $this->user->name . "'s Team",
    ]);

    // Set current team in session
    session(['current_team' => $team]);

    $this->actingAs($this->user);

    // Bypass plan-based authorization for team creation tests
    Gate::before(fn () => true);
});

test('team create page can be rendered', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get(route('team.create'));

    $response->assertStatus(200);
});

test('user can create a new team', function () {
    /** @var \Tests\TestCase $this */
    $component = Livewire::test('settings.team.⚡create')
        ->set('name', 'My New Team')
        ->call('createTeam');

    $component->assertHasNoErrors()
        ->assertRedirect(route('team.edit'));

    /** @var User $user */
    $user = $this->user;

    $this->assertDatabaseHas('team', [
        'name' => 'My New Team',
        'user_id' => $user->id,
    ]);
});

test('team name is required', function () {
    /** @var \Tests\TestCase $this */
    $component = Livewire::test('settings.team.⚡create')
        ->set('name', '')
        ->call('createTeam');

    $component->assertHasErrors(['name' => 'required']);
});

test('team name must not exceed 255 characters', function () {
    /** @var \Tests\TestCase $this */
    $component = Livewire::test('settings.team.⚡create')
        ->set('name', str_repeat('a', 256))
        ->call('createTeam');

    $component->assertHasErrors(['name' => 'max']);
});

test('team creation dispatches events', function () {
    /** @var \Tests\TestCase $this */
    $component = Livewire::test('settings.team.⚡create')
        ->set('name', 'Event Test Team')
        ->call('createTeam');

    $component->assertDispatched('team-created');
    $component->assertDispatched('alert');
});

