<?php

use App\Models\User;
use Tests\TestCase;

describe('Dashboard', function (): void {
    it('renders successfully', function (): void {
        /** @var TestCase $this */

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    });

    it('can be visited by authenticated users', function (): void {
        /** @var TestCase $this */

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
            $response->assertStatus(200);
    });
});
