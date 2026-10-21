<?php

namespace App\Mcp;

use App\Models\Diagram;
use App\Models\McpTokenTeam;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;

final class McpAccess
{
    public static function user(Request $request): ?User
    {
        $authUser = $request->user();

        return $authUser instanceof User ? $authUser : null;
    }

    public static function assertDiagramAbilityForTable(Request $request, Table $table, string $ability = 'update'): void
    {
        $diagram = Diagram::find($table->diagram_id);
        if (! $diagram) {
            throw (new ModelNotFoundException)->setModel(Diagram::class, [$table->diagram_id]);
        }

        self::assertDiagramAbility($request, $diagram, $ability);
    }

    public static function assertDiagramAbility(Request $request, Diagram $diagram, string $ability): void
    {
        $user = self::user($request);
        if ($user === null) {
            return;
        }

        Gate::forUser($user)->authorize($ability, $diagram);
    }

    public static function assertCanCreateDiagramOnTeam(Request $request, Team $team): void
    {
        $user = self::user($request);
        if ($user === null) {
            return;
        }

        Gate::forUser($user)->authorize('createDiagramOnTeam', $team);
        self::assertTokenAllowsTeam($request, $team);
    }

    /**
     * @return list<int>|null null = unauthenticated (local MCP): no filter
     */
    public static function teamIdsForUser(Request $request): ?array
    {
        $user = self::user($request);
        if ($user === null) {
            return null;
        }

        $teamIds = array_values($user->allTeams()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all());

        $tokenAllowed = self::tokenAllowedTeamIds($request);

        if ($tokenAllowed === null) {
            return $teamIds;
        }

        return array_values(array_intersect($teamIds, $tokenAllowed));
    }

    /**
     * Returns the team IDs the current OAuth token is restricted to,
     * or null when there are no restrictions (e.g. stdio personal tokens).
     *
     * @return list<int>|null
     */
    private static function tokenAllowedTeamIds(Request $request): ?array
    {
        $user = self::user($request);
        if ($user === null) {
            return null;
        }

        /** @var \Laravel\Passport\Token|null $token */
        $token = $user->token();
        if ($token === null) {
            return null;
        }

        return McpTokenTeam::allowedTeamIdsForToken((string) $token->id);
    }

    private static function assertTokenAllowsTeam(Request $request, Team $team): void
    {
        $allowed = self::tokenAllowedTeamIds($request);

        if ($allowed !== null && ! in_array((int) $team->id, $allowed, true)) {
            throw new AuthorizationException('This token is not allowed to access this team.');
        }
    }
}
