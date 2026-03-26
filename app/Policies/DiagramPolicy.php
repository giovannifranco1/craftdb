<?php

namespace App\Policies;

use App\Enums\Role as EnumsRole;
use App\Models\Diagram;
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
        $team = $diagram->team()->first();

        if (! $team) {
            return false;
        }

        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $currentTeam = Team::currentTeam();

        if (! $currentTeam) {
            return false;
        }

        $team = Team::find($currentTeam->id);

        return $team ? $this->createOnTeam($user, $team) : false;
    }

    /**
     * Create diagrams on a specific team (e.g. MCP / API without session current team).
     */
    public function createOnTeam(User $user, Team $team): bool
    {
        $team = Team::find($team->getKey());

        if (! $team) {
            return false;
        }

        if ($user->ownsTeam($team)) {
            return true;
        }

        if ($user->belongsToTeam($team)) {
            return $team->userHasRole($user, EnumsRole::EDITOR);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Diagram $diagram): bool
    {
        $team = $diagram->team()->first();

        if ($team && $user->ownsTeam($team)) {
            return true;
        }

        if ($team && $user->belongsToTeam($team)) {
            return $team->userHasRole($user, EnumsRole::EDITOR);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Diagram $diagram): bool
    {
        $team = $diagram->team()->first();

        if ($team && $user->ownsTeam($team)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can manage public sharing.
     */
    public function share(User $user, Diagram $diagram): bool
    {
        $team = $diagram->team()->first();

        if ($team && $user->ownsTeam($team)) {
            return true;
        }

        if ($team && $user->belongsToTeam($team)) {
            return $team->userHasRole($user, EnumsRole::EDITOR);
        }

        return false;
    }
}
