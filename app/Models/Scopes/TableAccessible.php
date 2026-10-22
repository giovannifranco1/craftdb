<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * @implements Scope<\App\Models\Table>
 */
class TableAccessible implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('diagram', function (Builder $q) : void {
            $user = Auth::user();
            if ($user === null) {
                $q->whereRaw('1 = 0');
                return;
            }
            $teamIds = $user->ownedTeams()->pluck('team.id');

            $q->whereIn('team_id', $teamIds);
        });
    }
}
