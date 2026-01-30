<?php

use App\Models\User;

test('confirm password screen can be rendered', function () {
    /** @var TestCase $this */

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertStatus(200);
});
