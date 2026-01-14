<?php

namespace App\Policies;

use App\Models\Diagram;
use App\Models\Team\Membership;
use App\Models\Team\Role;
use App\Models\Team\Team;
use App\Models\User;

class DiagramPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Diagram $diagram): bool
    {
        $team = Team::find($diagram->getTeamId());

        if (!$team) {
            return false;
        }

        // Owner can always view
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check if user is a member with viewer or editor role
        if ($user->belongsToTeam($team)) {
            $membership = Membership::forTeam($team->id, $user->id)->first();

            if (!$membership) {
                return false;
            }

            return in_array($membership->role->code, [Role::CODE_VIEWER, Role::CODE_EDITOR]);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $currentTeam = Team::currentTeam();

        if (!$currentTeam) {
            return false;
        }

        $team = Team::find($currentTeam->id);

        if (!$team) {
            return false;
        }

        // Owner can always create
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check if user is a member with editor role
        if ($user->belongsToTeam($team)) {
            $membership = Membership::forTeam($team->id, $user->id)->first();

            if (!$membership) {
                return false;
            }

            return in_array($membership->role->code, [Role::CODE_EDITOR, Role::CODE_VIEWER]);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Diagram $diagram): bool
    {
        $team = Team::find($diagram->getTeamId());

        if (!$team) {
            return false;
        }

        // Owner can always update
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check if user is a member with editor role
        if ($user->belongsToTeam($team)) {
            $membership = Membership::forTeam($team->id, $user->id)->first();

            if (!$membership) {
                return false;
            }

            return $membership->role->code === Role::CODE_EDITOR;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Diagram $diagram): bool
    {
        $team = Team::find($diagram->getTeamId());

        if (!$team) {
            return false;
        }

        // Owner can always delete
        if ($user->ownsTeam($team)) {
            return true;
        }

        // Check if user is a member with editor role
        if ($user->belongsToTeam($team)) {
            $membership = Membership::forTeam($team->id, $user->id)->first();

            if (!$membership) {
                return false;
            }

            return $membership->role->code === Role::CODE_EDITOR;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Diagram $diagram): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Diagram $diagram): bool
    {
        return false;
    }
}
