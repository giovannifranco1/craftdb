<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Team\Team;
use App\Support\DiagramLimits;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_teams')]
#[Description('List all workspaces (teams) the authenticated user belongs to. Call this before create_diagram or create_schema when team_id is unknown. Returns a numbered list — show it to the user exactly as returned, one per line, and ask which they want. Each line includes the team_id in brackets; use that value when creating diagrams. IMPORTANT: check the plan and diagram count for each workspace before proceeding — workspaces without a premium plan or that have reached the diagram limit cannot create new diagrams.')]
#[IsReadOnly(true)]
class ListTeamsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $user = McpAccess::user($request);

        if (! $user) {
            return Response::error('Unauthenticated.');
        }

        $teams = $user->allTeams()->values();

        $lines         = ['Available workspaces:'];
        $anyCanCreate  = false;
        $upgradeUrl    = rtrim(config('app.url'), '/').'/settings/billing';

        foreach ($teams as $index => $team) {
            /** @var Team $team */
            $team->loadMissing(['owner', 'users']);

            $hasPremium    = $user->hasTeamWorkspacePremiumAccess($team);
            $owner         = $team->owner;
            $limits        = $owner ? DiagramLimits::forUser($owner) : null;
            $diagramCount  = $team->diagrams()->count();
            $diagramLimit  = $limits ? $limits['diagram_limit'] : 0;
            $limitLabel    = $diagramLimit === PHP_INT_MAX ? 'unlimited' : (string) $diagramLimit;
            $limitReached  = $diagramLimit !== PHP_INT_MAX && $diagramCount >= $diagramLimit;

            if ($hasPremium && ! $limitReached) {
                $anyCanCreate = true;
            }

            $planLabel  = match (true) {
                ! $hasPremium => 'Free',
                $owner?->isTeamPlan() => 'Team',
                default => 'Pro',
            };

            $statusIcon = $hasPremium ? '✓' : '✗';
            $limitNote  = $limitReached ? ' ⚠ diagram limit reached ('.$diagramCount.'/'.$limitLabel.')' : ' ['.$diagramCount.'/'.$limitLabel.' diagrams]';

            $lines[] = ($index + 1).' - '.$team->name.' [team_id: '.$team->id.'] [plan: '.$planLabel.' '.$statusIcon.']'.$limitNote;
        }

        if (! $anyCanCreate) {
            $lines[] = '';
            $lines[] = '⚠ IMPORTANT: None of your workspaces can create new diagrams right now.';
            $lines[] = 'Possible reasons:';
            $lines[] = '  • No workspace has a Pro or Team plan (required to use the MCP integration).';
            $lines[] = '  • The diagram limit has been reached on all workspaces.';
            $lines[] = 'What to do:';
            $lines[] = '  • Upgrade your plan at: '.$upgradeUrl;
            $lines[] = '  • Or delete an existing diagram to free up a slot.';
            $lines[] = 'Stop here and inform the user — do not attempt to create or modify diagrams.';
        }

        return Response::text(implode("\n", $lines));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
