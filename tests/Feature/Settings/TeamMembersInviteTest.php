<?php

use App\Enums\Role;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var \Tests\TestCase $this */
    $this->user = User::factory()->create();

    $this->team = Team::create([
        'user_id' => $this->user->id,
        'name' => $this->user->name."'s Team",
    ]);

    session(['current_team' => $this->team]);

    $this->actingAs($this->user);

    Subscription::factory()
        ->for($this->user)
        ->withPrice(config('billing.plans.team_usd.price_id'))
        ->create([
            'type' => config('billing.subscription_name', 'default'),
        ]);

    User::clearSubscriptionCaches();
});

test('sequential member invites on same component persist two pending invitations', function () {
    /** @var \Tests\TestCase $this */
    $component = Livewire::test('settings.team.⚡members')
        ->set('email', 'first-invite@example.com')
        ->set('roleChoosen', Role::EDITOR)
        ->call('inviteMember')
        ->assertHasNoErrors();

    $component
        ->set('email', 'second-invite@example.com')
        ->call('inviteMember')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('invitation', 2);

    $this->assertDatabaseHas('invitation', [
        'team_id' => $this->team->id,
        'email' => 'first-invite@example.com',
    ]);

    $this->assertDatabaseHas('invitation', [
        'team_id' => $this->team->id,
        'email' => 'second-invite@example.com',
    ]);
});

test('cannot invite the same email twice while a pending invitation exists', function () {
    /** @var \Tests\TestCase $this */
    $email = 'duplicate@example.com';

    Livewire::test('settings.team.⚡members')
        ->set('email', $email)
        ->set('roleChoosen', Role::EDITOR)
        ->call('inviteMember')
        ->assertHasNoErrors();

    Livewire::test('settings.team.⚡members')
        ->set('email', $email)
        ->set('roleChoosen', Role::EDITOR)
        ->call('inviteMember')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('invitation', 1);
});

test('pending invitation duplicate check is case-insensitive for email', function () {
    /** @var \Tests\TestCase $this */
    Livewire::test('settings.team.⚡members')
        ->set('email', 'Same@Example.com')
        ->set('roleChoosen', Role::EDITOR)
        ->call('inviteMember')
        ->assertHasNoErrors();

    Livewire::test('settings.team.⚡members')
        ->set('email', 'same@example.com')
        ->set('roleChoosen', Role::EDITOR)
        ->call('inviteMember')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('invitation', 1);
});
