<?php

namespace Tests\Feature\Actions\Local;

use App\Actions\Local\EnsureLocalUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

/**
 * @property EnsureLocalUser $action
 */
beforeEach(function (): void {
    /** @var TestCase $this */
    $this->action = app(EnsureLocalUser::class);
});

describe('EnsureLocalUser', function (): void {
    it('NOAUTH13: creates the local user without a password', function () {
        /** @var TestCase $this */
        $result = $this->action->exec('local@craftdb.local');

        expect($result['user']->fresh()->password)->toBeNull();
    });

    it('DOCK08: running twice does not duplicate the user', function () {
        /** @var TestCase $this */
        $this->action->exec('local@craftdb.local');
        $result = $this->action->exec('local@craftdb.local');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and(User::where('email', 'local@craftdb.local')->count())->toBe(1)
            ->and($user->ownedTeams()->count())->toBe(1);
    });

    it('DOCK10: uses the given email', function () {
        /** @var TestCase $this */
        $result = $this->action->exec('dev@example.com');

        expect($result['user']->email)->toBe('dev@example.com');
    });

    it('DOCK06: reuses an existing unverified user without team, verifying it and creating the workspace', function () {
        /** @var TestCase $this */
        $existing = User::factory()->unverified()->create(['email' => 'existing@example.com']);

        $result = $this->action->exec('existing@example.com');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and($user->id)->toBe($existing->id)
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->ownedTeams()->count())->toBe(1);
    });
});
