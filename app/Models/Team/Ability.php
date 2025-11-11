<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use Jurager\Teams\Support\Facades\Teams as TeamsFacade;

/**
 * @property int $id
 * @property int $team_id
 * @property int $permission_id
 * @property string|null $title
 * @property string $entity_type
 * @property int $entity_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Team\Team $team
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ability whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ability extends Model
{
    protected $table = 'ability';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['id', 'permission_id', 'title', 'entity_id', 'entity_type'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable[] = Config::get('teams.foreign_keys.team_id', 'team_id');
    }

    /**
     * Get the team that the ability belongs to.
     *
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamsFacade::model('team'));
    }

    /**
     * Get all the users that are assigned this ability.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(TeamsFacade::model('user'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }

    /**
     * Get all the groups that are assigned this ability.
     */
    public function groups(): MorphToMany
    {
        return $this->morphedByMany(TeamsFacade::model('group'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }

    /**
     * Get all the roles that are assigned this ability.
     */
    public function roles(): MorphToMany
    {
        return $this->morphedByMany(TeamsFacade::model('role'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }
}
