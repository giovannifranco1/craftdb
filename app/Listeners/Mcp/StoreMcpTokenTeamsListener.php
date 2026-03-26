<?php

namespace App\Listeners\Mcp;

use App\Models\McpTokenTeam;
use Laravel\Passport\Events\AccessTokenCreated;

class StoreMcpTokenTeamsListener
{
    public function handle(AccessTokenCreated $event): void
    {
        $teamIds = session('mcp_pending_teams', []);

        if (empty($teamIds)) {
            return;
        }

        session()->forget('mcp_pending_teams');

        McpTokenTeam::storeForToken($event->tokenId, $teamIds);
    }
}
