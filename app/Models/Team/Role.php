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
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Ability> $abilities
 * @property-read int|null $abilities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Team\Team $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereTeamId($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    protected $table = 'role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['code', 'name', 'description'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'permissions',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'permissions',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->fillable[] = Config::get('teams.foreign_keys.team_id');
    }

    /**
     * Bootstrap any application services.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(static function ($role) {
            $role->permissions()->detach();
            $role->abilities()->detach();
        });

    }

    /**
     * Get the team that the role belongs to.
     *
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamsFacade::model('team'));
    }

    /**
     * Get the permissions that belongs to role.
     *
     * @return MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(TeamsFacade::model('permission'), 'entity', 'entity_permission');
    }

    /**
     * Get the abilities that belongs to role.
     *
     * @return MorphToMany
     */
    public function abilities(): MorphToMany
    {
        return $this->morphToMany(TeamsFacade::model('ability'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }
}
