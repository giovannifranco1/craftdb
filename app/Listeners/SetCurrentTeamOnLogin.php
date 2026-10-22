<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class SetCurrentTeamOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var User */
        $user = $event->user;
        $team = $user->firstTeam();

        session()->put('locale', $user->locale);
        app()->setLocale($user->locale);

        if ($team) {
            session(['current_team' => $team]);
        }
    }
}
