<?php

namespace App\Models\Concerns;

use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasTeams
{
    /**
     * Check if the user owns the given team.
     *
     * @param object $team
     * @return bool
     */
    public function ownsTeam(object $team): bool
    {
        return $this->id === $team->{$this->getForeignKey()};
    }

    /**
     * Retrieve all teams the user owns, ordered by name.
     *
     * @return Collection<int, Team>
     */
    public function allTeams(): Collection
    {
        return $this->ownedTeams()->orderBy('name')->get();
    }

    /**
     * The oldest team the user owns.
     */
    public function firstTeam(): ?Team
    {
        return $this->ownedTeams()->orderBy('id')->first();
    }

    /**
     * Teams the user owns.
     *
     * @return HasMany<Team, $this>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class)->withoutGlobalScopes();
    }
}
