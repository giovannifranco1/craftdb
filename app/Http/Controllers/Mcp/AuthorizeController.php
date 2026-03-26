<?php

namespace App\Http\Controllers\Mcp;

use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;

/**
 * Wraps Passport's authorization approval to capture the team selection
 * made on the custom authorize view.
 * Team IDs are stored in the session; the AccessTokenCreated listener
 * picks them up and writes them to mcp_token_teams.
 */
class AuthorizeController
{
    public function approve(Request $request, ApproveAuthorizationController $passport): mixed
    {
        $teamIds = array_filter(
            array_map('intval', (array) $request->input('mcp_teams', [])),
            fn (int $id) => $id > 0
        );

        session(['mcp_pending_teams' => array_values($teamIds)]);

        return app()->call([$passport, 'approve'], ['request' => $request]);
    }
}
