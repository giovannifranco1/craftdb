<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Jurager\Teams\Support\Facades\Teams as TeamsFacade;
use Exception;

class InviteController extends Controller
{
    /**
     * Accept the given invite.
     *
     * @param string|int $invitationId
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     * @throws Exception
     */
    public function inviteAccept(string|int $invitationId): \Illuminate\Foundation\Application|Redirector|Application|RedirectResponse
    {
        // Get the invitation model
        /** @var \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $invitationQuery */
        $invitationQuery = TeamsFacade::instance('invitation');
        $invitation = $invitationQuery->whereKey($invitationId)->firstOrFail();

        // Get the team from invitation
        /** @var object{team: mixed, id: mixed} $invitation */
        $team = $invitation->team;

        // Accept the invitation
        $team->inviteAccept($invitation->id);

        return redirect('/dashboard')->with('message', __('Success! You have accepted the invitation to join the :team team.', ['team' => $team->name]));
    }
}
