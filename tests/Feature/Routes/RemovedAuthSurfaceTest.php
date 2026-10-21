<?php

namespace Tests\Feature\Routes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(RefreshDatabase::class);

describe('Removed auth surface', function (): void {
    it('NOAUTH04: redirects /, /login and /register to the dashboard', function (string $uri) {
        /** @var TestCase $this */
        $this->get($uri)->assertRedirect('/dashboard');
    })->with(['/', '/login', '/register']);

    it('NOAUTH06/12/15: removed route :dataset is not registered', function (string $name) {
        expect(Route::has($name))->toBeFalse();
    })->with([
        'login.store', 'logout', 'register.store', 'password.request', 'password.email',
        'password.reset', 'password.update', 'verification.notice', 'verification.verify',
        'verification.send', 'password.confirm', 'two-factor.login', 'two-factor.show',
        'user-password.edit', 'auth.github.redirect', 'auth.github.callback',
        'auth.google.redirect', 'auth.google.callback',
        'feedback.index', 'invitation.accept', 'team.members.edit', 'team.invitations.edit',
    ]);

    it('NOAUTH12/16: middleware alias :dataset is gone', function (string $alias) {
        expect(app('router')->getMiddleware())->not->toHaveKey($alias);
    })->with(['role', 'super.root']);

    it('NOAUTH07: fortify and socialite are not installed', function () {
        expect(class_exists(\Laravel\Fortify\Fortify::class))->toBeFalse()
            ->and(class_exists(\Laravel\Socialite\Facades\Socialite::class))->toBeFalse();
    });

    it('NOAUTH15: invitations:cleanup command is gone', function () {
        expect(Artisan::all())->not->toHaveKey('invitations:cleanup');
    });
});
