<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Exception;

class Membership extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'team_user';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

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
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $teamId
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTeam($query, int $teamId, int $userId)
    {
        return $query->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->with('role');
    }
}
