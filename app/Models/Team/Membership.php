<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Exception;

#[Table('team_user', incrementing: true)]
class Membership extends Pivot
{
    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Get the role that the membership belongs to.
     *
     * @return BelongsTo
     * @throws Exception
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Scope a query to filter memberships by team and user.
     */
    #[Scope]
    public function forTeam(Builder $query, int $teamId, int $userId): Builder
    {
        return $query->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->with('role');
    }
}
