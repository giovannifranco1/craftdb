<?php

namespace App\Policies;

use App\Models\Table;
use App\Models\User;

class TablePolicy
{
     /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Table $table): bool
    {
        $team = $table->diagram?->team;
        return $team !== null && $user->ownsTeam($team);
    }
}
