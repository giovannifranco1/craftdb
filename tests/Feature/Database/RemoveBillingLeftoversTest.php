<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('FREE17: drops billing tables and columns and diagram.public_token', function () {
    expect(Schema::hasTable('subscriptions'))->toBeFalse()
        ->and(Schema::hasTable('subscription_items'))->toBeFalse()
        ->and(Schema::hasColumn('diagram', 'public_token'))->toBeFalse();

    foreach (['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'] as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse();
    }
});
