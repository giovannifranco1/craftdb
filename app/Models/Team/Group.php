<?php

namespace App\Models\Team;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Config;
use Jurager\Teams\Support\Facades\Teams as TeamsFacade;

/**
 * @property int $id
 * @property int|null $team_id
 * @property string $code
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Team\Ability> $abilities
 * @property-read int|null $abilities_count
 * @property-read Collection<int, \App\Models\Team\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Team\Team|null $team
 * @property-read Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Group whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Group extends Model
{
    protected $table = 'group';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['code', 'name'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
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

        static::deleting(static function ($group) {
            $group->permissions()->detach();
            $group->abilities()->detach();

        });
    }

    /**
     * Get the team that the group belongs to.
     *
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamsFacade::model('team'), Config::get('teams.foreign_keys.team_id'));
    }

    /**
     * Get all group users
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(TeamsFacade::model('user'), 'group_user', 'group_id', 'user_id');
    }

    /**
     * Get the permissions that belongs to group.
     *
     * @return MorphToMany
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(TeamsFacade::model('permission'), 'entity', 'entity_permission');
    }

    /**
     * Get the abilities that belongs to group.
     *
     * @return MorphToMany
     */
    public function abilities(): MorphToMany
    {
        return $this->morphToMany(TeamsFacade::model('ability'), 'entity', 'entity_ability')
            ->withPivot('forbidden')
            ->withTimestamps();
    }

    /**
     * Attach user or users to a group
     *
     * @param Collection|Model $user
     * @return bool
     */
    public function attachUser(Collection|Model $user): bool
    {
        // Convert user to collection if it is the only model
        $users = $user instanceof Collection ? $user : collect([$user]);

        // Filter only those users who are in the team
        $filteredUserIds = $users->filter(fn ($item) => $this->team->hasUser($item));

        // If there are users left after filtering, synchronize and return the result
        return $filteredUserIds->isNotEmpty() && $this->users()->syncWithoutDetaching($filteredUserIds->pluck('id')) > 0;
    }

    /**
     * Detach user or users from group
     */
    public function detachUser(Collection|Model $user): bool
    {
        // Convert user to collection if it is the only model
        $users = $user instanceof Collection ? $user : collect([$user]);

        // Filter only those users who are in the team
        $filteredUserIds  = $users->filter(fn ($item) => $this->team->hasUser($item));

        // If there are any users left after filtering, we execute detach and return the result
        return $filteredUserIds ->isNotEmpty() && $this->users()->detach($filteredUserIds->pluck('id')) > 0;
    }
}
