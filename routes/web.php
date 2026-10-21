<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Pages\DiagramController;
use Illuminate\Support\Facades\Route;

/** Landing Page */
// TODO(F7): remove with the landing page
Route::redirect('/', '/dashboard')->name('landing.index');
Route::redirect('/login', '/dashboard')->name('login');
Route::redirect('/register', '/dashboard')->name('register');
Route::get('/features', [LandingPageController::class, 'features'])->name('landing.features');
Route::get('/pricing', [LandingPageController::class, 'pricing'])->name('landing.pricing');
Route::get('/about', [LandingPageController::class, 'about'])->name('landing.about');
Route::get('/terms', fn () => view('pages.landing-page.terms'))->name('landing.terms');
Route::get('/policy', fn () => view('pages.landing-page.policy'))->name('landing.policy');

/** Dashboard */
Route::livewire('/dashboard', 'pages::dashboard')->lazy()->middleware(['auth'])->name('dashboard');

/** Settings */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'settings.profile')->name('profile.edit');
    Route::livewire('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Route::livewire('settings/language', 'settings.language')->name('language.edit');
    Route::livewire('settings/integrations', 'settings.integrations')->name('integrations.edit');

    Route::livewire('settings/workspaces', 'settings.workspaces')->name('workspaces.index');
});

/** Diagram */
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('{team_id}/diagram/{diagram:slug}', [DiagramController::class, 'show'])->name('diagram.show');
    Route::get('{team_id}/diagram/{diagram:slug}/export-json', [DiagramController::class, 'exportJson'])->name('diagram.export-json');
    Route::get('{team_id}/diagram/{diagram:slug}/export-laravel-migration', [DiagramController::class, 'exportLaravelMigration'])->name('diagram.export-laravel-migration');
    Route::post('{team_id}/diagram/{diagram:slug}/toggle-favorite', [DiagramController::class, 'toggleFavorite'])->name('diagram.toggle-favorite');
});
