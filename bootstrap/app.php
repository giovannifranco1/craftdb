<?php

use App\Exceptions\InvitationException;
use App\Http\Middleware\CheckTeamRole;
use App\Http\Middleware\EnsureSuperRoot;
use App\Http\Middleware\EnsureValidCurrentTeam;
use App\Http\Middleware\ForceJsonResponseForMcpOAuthRegistration;
use App\Http\Middleware\SetHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ValidateLoginTermsAcceptance;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckTeamRole::class,
            'super.root' => EnsureSuperRoot::class,
        ]);

        $middleware->web(append: [
            SetHeaders::class,
            SetLocale::class,
            ValidateLoginTermsAcceptance::class,
            EnsureValidCurrentTeam::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->append(ForceJsonResponseForMcpOAuthRegistration::class);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (InvitationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()
                ->route('dashboard')
                ->with('message', $e->getMessage())
                ->with('type', 'error');
        });
    })->create();
