<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use Jurager\Teams\Support\Facades\Teams as TeamsFacade;

/**
 * @property int $id
 * @property int $team_id
 * @property string|null $name
 * @property string $code
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereTeamId($value)
 * @mixin \Eloquent
 */
class Permission extends Model
{
    protected $table = 'permission';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['name', 'code'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable[] = Config::get('teams.foreign_keys.team_id');
    }

    /**
     * Get all the groups that are assigned this permission.
     */
    public function groups(): MorphToMany
    {
        return $this->morphedByMany(TeamsFacade::model('group'), 'entity', 'entity_permission');
    }

    /**
     * Get all the roles that are assigned this permission.
     */
    public function roles(): MorphToMany
    {
        return $this->morphedByMany(TeamsFacade::model('role'), 'entity', 'entity_permission');
    }
}
