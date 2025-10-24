<?php

namespace App\Models\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Jurager\Teams\Traits\HasMembers;

class Team extends Model
{
    use HasMembers, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['user_id', 'name'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['roles.permissions', 'groups.permissions'];

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('teams.tables.teams', 'teams');
    }


    public static function currentTeam()
    {
        $team = session('current_team');

        if (!is_null($team)) {
            return $team;
        }

        /** @var User */
        $user = auth()->user;

        return $user->ownedTeams()->get()->first();
    }
}
