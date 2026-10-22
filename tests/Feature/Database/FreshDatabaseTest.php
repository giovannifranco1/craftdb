<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('NOAUTH11: a fresh database has no users', function () {
    expect(User::count())->toBe(0);
});

it('NOAUTH20: db:seed runs without errors', function () {
    /** @var TestCase $this */
    $this->artisan('db:seed')->assertSuccessful();
});
