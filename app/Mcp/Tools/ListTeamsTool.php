<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Team\Team;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_teams')]
#[Description('List all workspaces (teams) the authenticated user belongs to. Call this before create_diagram or create_schema when team_id is unknown. Returns a numbered list — show it to the user exactly as returned, one per line, and ask which they want. Each line includes the team_id in brackets; use that value when creating diagrams.')]
#[IsReadOnly(true)]
class ListTeamsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $user = McpAccess::user();

        $teams = $user->allTeams()->values()->loadCount('diagrams');

        $lines = ['Available workspaces:'];

        foreach ($teams as $index => $team) {
            /** @var Team $team */
            $lines[] = ($index + 1).' - '.$team->name.' [team_id: '.$team->id.'] ['.$team->diagrams_count.' diagrams]';
        }

        return Response::text(implode("\n", $lines));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
