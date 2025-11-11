<?php

namespace App\Models\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Jurager\Teams\Support\Facades\Teams as TeamsFacade;

/**
 * @property int $id
 * @property int $team_id
 * @property int $role_id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team\Role $role
 * @property-read \App\Models\Team\Team $team
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invitation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Invitation extends Model
{
    protected $table = 'invitation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['team_id', 'role_id', 'email'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable[] = Config::get('teams.foreign_keys.team_id');
    }

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamsFacade::model('team'), Config::get('teams.foreign_keys.team_id'));
    }

    /**
     * Get the team role that the invitation belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(TeamsFacade::model('role'), Config::get('teams.foreign_keys.team_id'));
    }

    /**
     * Get the user that the invitation belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TeamsFacade::model('user'), 'email', 'email');
    }
}
