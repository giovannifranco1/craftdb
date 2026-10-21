<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param object $team
     * @return bool
     */
    public function view(User $user, object $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can update the team information.
     *
     * @param User $user
     * @param object $team
     * @return bool
     */
    public function updateTeam(User $user, object $team): bool
    {
        return $user->ownsTeam($team);
    }
}
