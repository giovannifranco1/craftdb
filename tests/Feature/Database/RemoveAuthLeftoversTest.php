<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('NOAUTH18: drops auth columns from users and keeps password nullable', function () {
    foreach ([
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
        'github_id', 'github_token', 'github_refresh_token',
        'google_id', 'google_token', 'google_refresh_token',
    ] as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse();
    }

    expect(Schema::hasColumn('users', 'password'))->toBeTrue();
});

it('NOAUTH18: drops invitation, role and password reset tables and team_user', function () {
    expect(Schema::hasTable('invitation'))->toBeFalse()
        ->and(Schema::hasTable('role'))->toBeFalse()
        ->and(Schema::hasTable('password_reset_tokens'))->toBeFalse()
        ->and(Schema::hasTable('team_user'))->toBeFalse(); // dropped in F4 (WS18)
});
