<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Pages\DiagramController;
use App\Livewire\MyFavorites;
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
Route::livewire('/my-favorites', MyFavorites::class)->middleware(['auth'])->name('dashboard.my-favorites');

/** Settings */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'settings.profile')->name('profile.edit');
    Route::livewire('settings/billing', 'settings.billing')->name('billing.index');
    Route::get('settings/billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('settings/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::get('settings/billing/success', [BillingController::class, 'success'])->name('billing.success');
    Route::livewire('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Route::livewire('settings/language', 'settings.language')->name('language.edit');
    Route::livewire('settings/integrations', 'settings.integrations')->name('integrations.edit');

    Route::livewire('settings/team/create', 'settings.team.⚡create')->name('team.create');
    Route::livewire('settings/team', 'settings.team')->name('team.edit');
});

/** Diagram */
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('{team_id}/diagram/{diagram:slug}', [DiagramController::class, 'show'])->name('diagram.show');
    Route::get('{team_id}/diagram/{diagram:slug}/export-json', [DiagramController::class, 'exportJson'])->name('diagram.export-json');
    Route::get('{team_id}/diagram/{diagram:slug}/export-laravel-migration', [DiagramController::class, 'exportLaravelMigration'])->name('diagram.export-laravel-migration');
    Route::post('{team_id}/diagram/{diagram:slug}/toggle-favorite', [DiagramController::class, 'toggleFavorite'])->name('diagram.toggle-favorite');
});

/** Public Diagram (no auth required) */
Route::get('/diagram/public/{token}', [DiagramController::class, 'showPublic'])->name('diagram.public');

/** Debug cf-ipcountry (local only) */
if (app()->environment('local')) {
    Route::get('/debug-cf', function () {
        return [
            'all_headers' => request()->headers->all(),
            'country'     => request()->header('cf-ipcountry'),
            'ip'          => request()->ip(),
            'server'      => request()->server->all(),
        ];
    });
}
