<?php

namespace App\Models\Team;

use App\Models\Diagram;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $owner
 *
 * @method static \Database\Factories\Team\TeamFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Team whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Table('team')]
#[Fillable(['user_id', 'name'])]
class Team extends Model
{
    use HasFactory;

    /**
     * Creates a new instance of the model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function currentTeam(): ?self
    {
        /** @var User|null */
        /** @disregard  */
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $sessionTeam = session('current_team');

        if ($sessionTeam instanceof self) {
            $team = $user->ownedTeams()->find($sessionTeam->getKey());

            if ($team) {
                return $team;
            }
        }

        $team = $user->firstTeam();
        session(['current_team' => $team]);

        return $team;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Diagram, $this>
     */
    public function diagrams(): HasMany
    {
        return $this->hasMany(Diagram::class, 'team_id');
    }
}
