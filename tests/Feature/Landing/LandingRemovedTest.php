<?php

use App\Http\Middleware\AuthenticateLocalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

it('LP01: landing and login/register routes are gone, / redirects to dashboard', function () {
    foreach (['landing.index', 'landing.features', 'landing.pricing', 'landing.about', 'landing.terms', 'landing.policy', 'login', 'register'] as $name) {
        expect(Route::has($name))->toBeFalse($name);
    }

    foreach (['/features', '/pricing', '/about', '/terms', '/policy', '/login', '/register'] as $uri) {
        $this->get($uri)->assertNotFound();
    }

    $this->get('/')->assertRedirect('/dashboard');
});

it('LP02: guests hitting an auth route are redirected to /dashboard without exception', function () {
    Route::middleware(['web', 'auth'])->get('/_lp02', fn () => 'ok');

    $this->withoutMiddleware(AuthenticateLocalUser::class)
        ->get('/_lp02')
        ->assertRedirect('/dashboard');
});

it('LP03: landing code and assets no longer exist', function () {
    expect(class_exists(\App\Http\Controllers\LandingPageController::class))->toBeFalse();

    foreach ([
        config_path('landingpage.php'),
        resource_path('css/landing-page.css'),
        resource_path('views/pages/landing-page'),
        resource_path('views/components/landing-page'),
        resource_path('views/components/layouts/landing-page.blade.php'),
        lang_path('en/landing-page.php'),
        lang_path('pt_BR/landing-page-legal.php'),
        resource_path('markdown'),
        resource_path('images/landing-page'),
        resource_path('images/auth'),
        public_path('preview.png'),
    ] as $path) {
        expect(file_exists($path))->toBeFalse($path);
    }

    expect(file_get_contents(base_path('vite.config.js')))->not->toContain('landing-page.css');
});
