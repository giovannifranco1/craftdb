<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TableAccessible implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereHas('diagram', function (Builder $q) : void {
            $teamIds = Auth::user()
                ->teams()
                ->pluck('team.id')
                ->merge(Auth::user()->ownedTeams()->pluck('team.id'))
                ->unique();

            $q->whereIn('team_id', $teamIds);
        });
    }
}
