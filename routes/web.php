<?php

use App\Http\Controllers\InviteController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Pages\DiagramController;
use App\Livewire\Dashboard;
use App\Livewire\MyFavorites;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

/** Landing Page */
Route::get('/', [LandingPageController::class, 'index'])->name('landing.index');
Route::get('/features', [LandingPageController::class, 'features'])->name('landing.features');
Route::get('/pricing', [LandingPageController::class, 'pricing'])->name('landing.pricing');
Route::get('/about', [LandingPageController::class, 'about'])->name('landing.about');

/** Homepage */
Route::get('/', fn () => view('landing-page.index'))->name('home');

/** Dashboard */
Route::get('/dashboard', Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/my-favorites', MyFavorites::class)->middleware(['auth', 'verified'])->name('dashboard.my-favorites');

/** Invitations */
Route::middleware(['auth'])->group(function () {
    Route::get('/invite/{invitation}/accept', [InviteController::class, 'inviteAccept'])->name('invite.accept');
});

/** Settings */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
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
        Volt::route('settings/team/create', 'settings.team.create')->name('team.create');
        Volt::route('settings/team', 'settings.team')->name('team.edit');
        Volt::route('settings/team/members', 'settings.team.members')->name('team.members.edit');
        Volt::route('settings/team/invitations', 'settings.team.invitations')->name('team.invitations.edit');
    });
});

/** Diagram */
Route::middleware(['auth', 'role:viewer|editor', 'role:team_id', 'throttle:60,1'])->group(function () {
    Route::get('{team_id}/diagram/{diagram:slug}', [DiagramController::class, 'show'])->name('diagram.show');
    Route::post('{team_id}/diagram/{diagram:slug}/toggle-favorite', [DiagramController::class, 'toggleFavorite'])->name('diagram.toggle-favorite');
});
