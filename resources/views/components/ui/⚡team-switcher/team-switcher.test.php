<?php

use App\Models\Team\Team;
use App\Models\User;
use Livewire\Livewire;

it('renders successfully', function (): void {
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $user->id]);
    $team2 = Team::factory()->create(['user_id' => $user->id]);

    /** @var TestCase $this */
    $this->actingAs($user);

    Livewire::test('ui.⚡team-switcher')
        ->assertOk();
});

it('can switch teams', function (): void {
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['user_id' => $user->id]);
    $team2 = Team::factory()->create(['user_id' => $user->id]);

    /** @var TestCase $this */
    $this->actingAs($user);

    // Switch to team2
    Livewire::test('ui.⚡team-switcher')
        ->call('switchTeam', $team2->id)
        ->assertDispatched('alert');

    // Check if current team was updated
    expect(session('current_team')['id'])->toBe($team2->id);
});

it('shows only teams user has access to', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $userTeam = Team::factory()->create(['user_id' => $user->id]);
    $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);

    /** @var TestCase $this */
    $this->actingAs($user);

    $component = Livewire::test('ui.⚡team-switcher');

    // Should only show user's team
    expect($component->get('getAllTeams')->count())->toBe(1);
    expect($component->get('getAllTeams')->first()->id)->toBe($userTeam->id);
});

it('is always visible with the current workspace name and a manage link', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id, 'name' => 'Only one']);

    /** @var TestCase $this */
    $this->actingAs($user);
    session(['current_team' => $team]);

    Livewire::test('ui.⚡team-switcher')
        ->assertSee('Only one')
        ->assertSee(route('workspaces.index'), false);
});
