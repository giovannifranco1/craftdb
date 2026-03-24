<?php

namespace App\Models\Team;

use App\Enums\Role as EnumsRole;
use App\Exceptions\InvitationException;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * @property int $id
 * @property int $team_id
 * @property int $role_id
 * @property string $token
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Team\Role $role
 * @property-read EnumsRole $role_enum
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
#[Table('invitation')]
#[Fillable(['team_id', 'role_id', 'email'])]
class Invitation extends Model
{
    const MAX_INVITATIONS_PER_DAY = 12;

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
            throw InvitationException::alreadyAccepted();
        }

        if ($this->expires_at < now()) {
            throw InvitationException::expired();
        }

        if ($this->cancelled_at) {
            throw InvitationException::cancelled();
        }

        $this->update(['accepted_at' => now()]);

        $this->team->inviteAccept($this->token);
    }

    public function cancel(): void
    {
        $this->update(['cancelled_at' => now()]);
    }

    public function roleEnum(): Attribute
    {
        return Attribute::make(
            get: fn (): EnumsRole => EnumsRole::from($this->role()->first()->code)
        );
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

        $this->team->inviteUser($this->email, $this->role_enum);
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }
}
