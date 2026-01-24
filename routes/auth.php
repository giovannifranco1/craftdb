<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/** GitHub Authentication */
Route::get('/auth/redirect', fn () => Socialite::driver('github')->redirect())->name('auth.github.redirect');
Route::get('/auth/callback', [AuthController::class, 'githubCallback'])->name('auth.github.callback');

/** Google Authentication */
Route::get('/auth/google/redirect', fn () => Socialite::driver('google')->redirect())->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');

/** Invitations */
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/invitation/{invitation:token}/accept', [InvitationController::class, 'accept'])->name('invitation.accept');
});
