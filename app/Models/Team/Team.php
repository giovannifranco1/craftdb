<?php

namespace App\Models\Team;

use App\Models\Concerns\HasMembers as ConcernsHasMembers;
use App\Models\Diagram;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Ability> $abilities
 * @property-read int|null $abilities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Group> $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team\Invitation> $invitations
 * @property-read int|null $invitations_count
 * @property-read User|null $owner
 *@property-read \App\Models\Team\Membership|null $membership
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\Team\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUserId($value)
 * @mixin \Eloquent
 */
#[Table('team')]
#[Fillable(['user_id', 'name'])]
class Team extends Model
{
    use ConcernsHasMembers, HasFactory;

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function currentTeam(): ?self
    {
        $team = session('current_team');

        if (!is_null($team)) {
            return $team;
        }

        /** @var User|null */
        /** @disregard  */
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        $ownedTeam = $user->ownedTeams()->first();

        session(['current_team' => $ownedTeam]);

        return $ownedTeam;
    }

    /**
     * @return HasMany<Diagram, $this>
     */
    public function diagrams(): HasMany
    {
        return $this->hasMany(Diagram::class, 'team_id');
    }
}
