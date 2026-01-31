<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateUserFromOAuth;
use App\Exceptions\EmailAlreadyRegisteredException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function googleCallback(CreateUserFromOAuth $action): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        try {
            $user = $action->exec(User::PROVIDERS['google'], $googleUser);
        } catch (EmailAlreadyRegisteredException $e) {
            return redirect()->route('login')->withErrors(['email' => $e->getMessage()]);
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }

    public function githubCallback(CreateUserFromOAuth $action): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        try {
            $user = $action->exec(User::PROVIDERS['github'], $githubUser);
        } catch (EmailAlreadyRegisteredException $e) {
            return redirect()->route('login')->withErrors(['email' => $e->getMessage()]);
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
