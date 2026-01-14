<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * @return bool
     */
    public function viewAny(): bool
    {
        return true;
    }

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
     * Determine whether the user can create models.
     */
    public function create(object $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
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
     * Determine whether the user can add team members.
     *
     * @param User $user
     * @param object $team
     * @return bool
     */
    public function addTeamMember(User $user, object $team): bool
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can update team member
     *
     * @param User $user
     * @param object $team
     * @return bool
     */
    public function updateTeamMember(User $user, object $team): bool
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can remove team members.
     *
     * @param User $user
     * @param object $team
     * @return bool
     */
    public function removeTeamMember(User $user, object $team): bool
    {
        return $user->ownsTeam($team);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param object $team
     * @return bool
     */
    public function delete(User $user, object $team): bool
    {
        return $user->ownsTeam($team);
    }
}
