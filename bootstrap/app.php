<?php

use App\Http\Middleware\CheckTeamRole;
use App\Http\Middleware\EnsureSuperRoot;
use App\Http\Middleware\SetHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ValidateLoginTermsAcceptance;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
