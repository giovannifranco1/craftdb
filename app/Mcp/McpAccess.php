<?php

namespace App\Mcp;

use App\Models\Diagram;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class McpAccess
{
    /**
     * The acting user: the authenticated one (tests/session) or the local user.
     * Never null — ownership checks always run.
     */
    public static function user(): User
    {
        $authUser = Auth::user();

        if ($authUser instanceof User) {
            return $authUser;
        }

        /** @var User */
        return app('craftdb.local-user');
    }

    public static function assertDiagramAbilityForTable(Table $table, string $ability = 'update'): void
    {
        $diagram = Diagram::find($table->diagram_id);
        if (! $diagram) {
            throw (new ModelNotFoundException)->setModel(Diagram::class, [$table->diagram_id]);
        }

        self::assertDiagramAbility($diagram, $ability);
    }

    public static function assertDiagramAbility(Diagram $diagram, string $ability): void
    {
        Gate::forUser(self::user())->authorize($ability, $diagram);
    }

    public static function assertCanCreateDiagramOnTeam(Team $team): void
    {
        Gate::forUser(self::user())->authorize('createDiagramOnTeam', $team);
    }

    /**
     * @return list<int>
     */
    public static function teamIdsForUser(): array
    {
        return array_values(self::user()->allTeams()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all());
    }
}
