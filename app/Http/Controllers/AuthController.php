<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateUserFromOAuth;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function googleCallback(CreateUserFromOAuth $action): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $user = $action->exec(User::PROVIDERS['google'], $googleUser);

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }

    public function githubCallback(CreateUserFromOAuth $action): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();
        $user = $action->exec(User::PROVIDERS['github'], $githubUser);
        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
