<?php

namespace Tests\Feature\Middleware;

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['craftdb.local_user.email' => 'local@craftdb.local']);
});

describe('AuthenticateLocalUser', function (): void {
    it('NOAUTH01: authenticates the local user on a request without session user', function () {
        /** @var TestCase $this */
        $this->get('/dashboard')->assertOk();

        expect(Auth::user()?->email)->toBe('local@craftdb.local');
    });

    it('NOAUTH02: creates the local user when it does not exist yet', function () {
        /** @var TestCase $this */
        expect(User::where('email', 'local@craftdb.local')->exists())->toBeFalse();

        $this->get('/dashboard')->assertOk();

        $user = User::where('email', 'local@craftdb.local')->firstOrFail();
        expect($user->ownedTeams()->count())->toBe(1);
    });

    it('NOAUTH03: logs in only once per session, keeping the current team', function () {
        /** @var TestCase $this */
        Event::fake([Login::class]);

        $this->get('/dashboard')->assertOk();
        $user = Auth::user();
        $second = Team::factory()->create(['user_id' => $user->id, 'name' => 'Second']);
        session(['current_team' => $second]);

        $this->get('/dashboard')->assertOk();

        Event::assertDispatchedTimes(Login::class, 1);
        expect(session('current_team')->id)->toBe($second->id);
    });

    it('NOAUTH05: settings pages respond without redirecting to login', function () {
        /** @var TestCase $this */
        $this->get('/settings/profile')->assertOk();
        $this->get('/settings/appearance')->assertOk();
    });
});
