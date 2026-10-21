<?php

use App\Http\Middleware\AuthenticateLocalUser;
use App\Http\Middleware\EnsureValidCurrentTeam;
use App\Http\Middleware\SetLocale;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
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
        $middleware->web(append: [
            AuthenticateLocalUser::class,
            SetLocale::class,
            EnsureValidCurrentTeam::class,
        ]);

        $middleware->prependToPriorityList(before: AuthenticatesRequests::class, prepend: AuthenticateLocalUser::class);

        $middleware->redirectGuestsTo('/dashboard');

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
