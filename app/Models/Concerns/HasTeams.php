<?php

namespace App\Models\Concerns;

use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Exception;

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
     * Retrieve all teams the user owns or belongs to.
     *
     * @return Collection<int, \App\Models\Team\Team>
     */
    public function allTeams(): Collection
    {
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Retrieve all teams the user owns.
     *
     * @return HasMany<\App\Models\Team\Team, $this>
     * @throws Exception
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class)->withoutGlobalScopes();
    }

    /**
     * Check if the user belongs to the specified team.
     *
     * @param object $team
     * @return bool
     * @throws Exception
     */
    public function belongsToTeam(object $team): bool
    {
        // @phpstan-ignore-next-line
        return $this->ownsTeam($team) || $this->teams()->where('team_id', $team->id)->exists();
    }

    /**
     * Retrieve all teams the user belongs to.
     *
     * @return BelongsToMany<\App\Models\Team\Team, $this, \Illuminate\Database\Eloquent\Relations\Pivot, 'membership'>
     * @throws Exception
     */
    public function teams(): BelongsToMany
    {
        return $this
            ->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id')
            ->withoutGlobalScopes()
            ->withTimestamps()
            ->as('membership');
    }
}
