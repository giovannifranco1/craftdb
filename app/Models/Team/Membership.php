<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Config;
use Jurager\Teams\Support\Facades\Teams as TeamsFacade;
use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team\Role $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Membership whereUserId($value)
 * @mixin \Eloquent
 */
class Membership extends Pivot
{
    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'membership';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'role',
    ];

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('teams.tables.team_user', 'team_user');
    }

    /**
     * Get the role that the membership belongs to.
     *
     * @return BelongsTo
     * @throws Exception
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(TeamsFacade::model('role'), 'role_id', 'id');
    }

    public function scopeForTeam(Builder $query, int $teamId, int $userId): Builder
    {
        return $query->where('team_id', $teamId)
            ->where('user_id', $userId)
            ->with('role');
    }
}
