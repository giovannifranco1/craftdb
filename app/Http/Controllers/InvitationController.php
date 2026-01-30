<?php

namespace App\Http\Controllers;

use App\Models\Team\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Exception;
use Illuminate\Support\Facades\Session;

class InvitationController extends Controller
{
    /**
     * Accept the given invite.
     *
     * @param Invitation $invitation find by tokens
     * @return RedirectResponse
     * @throws Exception
     */
    public function accept(Invitation $invitation): RedirectResponse
    {
        $invitation->accept();

        Session::regenerate();

        session(['current_team' => $invitation->team]);

        return redirect()->route('dashboard')->with('message', __('Success! You have accepted the invitation to join the :team team.', ['team' => $invitation->team->name]));
    }
}
