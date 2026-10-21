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
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can update the team information.
     *
     * @param User $user
     * @param object $team
     * @return bool
     */
    public function update(User $user, object $team): bool
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can delete the workspace.
     */
    public function delete(User $user, object $team): bool
    {
        return $user->ownsTeam($team);
    }
}
