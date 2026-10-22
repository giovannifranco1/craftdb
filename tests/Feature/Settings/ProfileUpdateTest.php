<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('NOAUTH10: profile name can be updated', function () {
    /** @var TestCase $this */
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test('settings.profile')
        ->set('name', 'Novo')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->name)->toBe('Novo');
});

test('NOAUTH10: profile page has no email, verification or delete account UI', function () {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->create());

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('wire:model="email"', false)
        ->assertDontSee('unverified')
        ->assertDontSee('verification')
        ->assertDontSee('delete-user');
});
