<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('password can be updated', function () {
    /** @var TestCase $this */

    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test('settings.password')
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    /** @var TestCase $this */

    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test('settings.password')
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasErrors(['current_password']);
});
