<?php

namespace Tests\Feature\Actions\Local;

use App\Actions\Local\EnsureLocalUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
    it('DOCK06: creates a verified local user with a workspace', function () {
        /** @var TestCase $this */
        $result = $this->action->exec('local@craftdb.local', 'craftdb');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeTrue()
            ->and($user->email)->toBe('local@craftdb.local')
            ->and($user->email_verified_at)->not->toBeNull()
            ->and(Hash::check('craftdb', $user->password))->toBeTrue()
            ->and($user->ownedTeams()->count())->toBe(1)
            ->and($user->ownedTeams()->first()->name)->toBe('Local Workspace');
    });

    it('DOCK08: running twice does not duplicate the user nor change the password', function () {
        /** @var TestCase $this */
        $this->action->exec('local@craftdb.local', 'craftdb');
        $result = $this->action->exec('local@craftdb.local', 'outra-senha');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and(User::where('email', 'local@craftdb.local')->count())->toBe(1)
            ->and(Hash::check('craftdb', $user->password))->toBeTrue()
            ->and($user->ownedTeams()->count())->toBe(1);
    });

    it('DOCK10: uses the given email and password', function () {
        /** @var TestCase $this */
        $result = $this->action->exec('dev@example.com', 'segredo123');

        expect($result['user']->email)->toBe('dev@example.com')
            ->and(Hash::check('segredo123', $result['user']->fresh()->password))->toBeTrue();
    });

    it('DOCK06: reuses an existing unverified user without team, verifying it and creating the workspace', function () {
        /** @var TestCase $this */
        $existing = User::factory()->unverified()->create(['email' => 'existing@example.com']);

        $result = $this->action->exec('existing@example.com', 'craftdb');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and($user->id)->toBe($existing->id)
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->ownedTeams()->count())->toBe(1);
    });

    it('DOCK06: reuses the super root when the email matches, keeping its single team', function () {
        /** @var TestCase $this */
        $superRoot = User::findOrFail(1);

        $result = $this->action->exec($superRoot->email, 'craftdb');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and($user->id)->toBe(1)
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->ownedTeams()->count())->toBe(1);
    });
});
