<?php

namespace App\Models\Concerns;

use App\Models\Team\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasMembers
{
    /**
     * Get the owner of the team.
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all users associated with the team.
     *
     * @return BelongsToMany<User, Membership>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Check if the team includes a given user.
     *
     * @param  object  $user
     * @return bool
     */
    public function hasUser(object $user): bool
    {
        return $this->users->contains($user) || $user->ownsTeam($this);
    }
}
