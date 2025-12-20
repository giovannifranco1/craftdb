<?php

use App\Models\User;

test('guests are redirected to the login page', function (): void {
    /** @var TestCase $this */

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function (): void {
    /** @var TestCase $this */

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertStatus(200);
});
