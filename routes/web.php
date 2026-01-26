<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Pages\DiagramController;
use App\Livewire\MyFavorites;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

/** Landing Page */
Route::get('/', [LandingPageController::class, 'index'])->name('landing.index');
Route::get('/features', [LandingPageController::class, 'features'])->name('landing.features');
Route::get('/pricing', [LandingPageController::class, 'pricing'])->name('landing.pricing');
Route::get('/about', [LandingPageController::class, 'about'])->name('landing.about');

/** Landing Page */
Route::get('/', fn () => view('pages::landing-page.index'))->name('home');

/** Dashboard */
Route::livewire('/dashboard', 'pages::dashboard')->lazy()->middleware(['auth', 'verified'])->name('dashboard');
Route::livewire('/my-favorites', MyFavorites::class)->middleware(['auth', 'verified'])->name('dashboard.my-favorites');

/** Settings */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'settings.profile')->name('profile.edit');
    Route::livewire('settings/password', 'settings.password')->name('user-password.edit');
    Route::livewire('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Route::livewire('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::middleware(['auth'])->group(function () {
        Route::livewire('settings/team/create', 'settings.team.create')->name('team.create');
        Route::livewire('settings/team', 'settings.team')->name('team.edit');
        Route::livewire('settings/team/members', 'settings.team.members')->name('team.members.edit');
        Route::livewire('settings/team/invitations', 'settings.team.invitations')->name('team.invitations.edit');
    });
});

/** Diagram */
Route::middleware(['auth', 'role:viewer|editor|owner', 'throttle:60,1'])->group(function () {
    Route::get('{team_id}/diagram/{diagram:slug}', [DiagramController::class, 'show'])->name('diagram.show');
    Route::post('{team_id}/diagram/{diagram:slug}/toggle-favorite', [DiagramController::class, 'toggleFavorite'])->name('diagram.toggle-favorite');
    Route::post('{team_id}/diagram/{diagram:slug}/make-public', [DiagramController::class, 'makePublic'])->name('diagram.make-public');
    Route::delete('{team_id}/diagram/{diagram:slug}/revoke-public', [DiagramController::class, 'revokePublic'])->name('diagram.revoke-public');
});

/** Public Diagram (no auth required) */
Route::get('/diagram/public/{token}', [DiagramController::class, 'showPublic'])->name('diagram.public');

/** Authentication Routes */
require_once __DIR__ . '/auth.php';
