<?php

namespace App\Models\Team;

use App\Enums\Role as EnumsRole;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Str;

use function Symfony\Component\Translation\t;

/**
 * @property int $id
 * @property int $team_id
 * @property int $role_id
 * @property string $token
 * @property EnumsRole $role
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
    const MAX_INVITATIONS_PER_DAY = 12;
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
    }

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    /**
     * Get the team role that the invitation belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Get the user that the invitation belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->whereNotNull('cancelled_at');
    }

    public function accept(): void
    {
        if ($this->accepted_at) {
            throw new RuntimeException(__('Invitation already accepted.'));
        }

        if ($this->expires_at < now()) {
            throw new RuntimeException(__('Invitation expired.'));
        }

        if ($this->cancelled_at) {
            throw new RuntimeException(__('Invitation cancelled.'));
        }

        $this->update(['accepted_at' => now()]);

        $this->team->inviteAccept($this->token);
    }

    public function cancel(): void
    {
        $this->update(['cancelled_at' => now()]);
    }

    public function getRoleAttribute(): EnumsRole
    {
        return EnumsRole::from($this->role()->first()->code);
    }

    public function resend(): void
    {
        if ($this->team->hasReachedMaxInvitationsPerDay($this->email)) {
            throw new RuntimeException(__('You have reached the maximum number of invitations per day.'));
        }

        if (is_null($this->user)) {
            return;
        }

        $this->update(['cancelled_at' => now()]);

        $this->team->inviteUser($this->email, $this->role);
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }
}
