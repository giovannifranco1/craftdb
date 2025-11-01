<?php

use App\Http\Controllers\Pages\DiagramController;
use App\Http\Controllers\Pages\HomeController;
use App\Http\Controllers\Pages\TesteController;
use App\Livewire\Dashboard;
use App\Livewire\MyFavorites;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

/** Dashboard */
Route::get('/', Dashboard::class)->middleware(['auth', 'verified'])->name('home');
Route::get('/my-favorites', MyFavorites::class)->middleware(['auth', 'verified'])->name('home.my-favorites');

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
});

/** Diagram */
Route::middleware(['auth', 'role:team_id'])->group(function () {
    Route::get('{team_id}/teste', [TesteController::class, 'index']);
    Route::get('{team_id}/canvas', [TesteController::class, 'canvas']);
    Route::get('{team_id}/diagram/{diagram:slug}', [DiagramController::class, 'show'])->name('diagram.show');
});
