<?php

namespace App\Models\Concerns;

use App\Enums\Role as EnumsRole;
use App\Models\Team\Invitation;
use App\Models\Team\Membership;
use App\Models\Team\Owner;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use RuntimeException;
use Str;

trait HasMembers
{
    /**
     * Get the owner of the team.
     *
     * @return BelongsTo
     * @throws Exception
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all users associated with the team.
     *
     * @return BelongsToMany<User, Membership>
     * @throws Exception
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Get all members of the team.
     *
     * @return BelongsToMany<Membership>
     * @throws Exception
     */
    public function members(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, Membership::class, 'team_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Get all pending invitations for the team.
     *
     * @return HasMany<Invitation>
     * @throws Exception
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'team_id', 'id');
    }



    /**
     * Retrieve all users in the team, including the owner.
     *
     * @return Collection
     */
    public function allUsers(): Collection
    {
        return $this->users->merge([$this->owner]);
    }

    /**
     * Check if the team includes a given user.
     *
     * @param  object  $user
     * @return bool
     */
    public function hasUser(object $user): bool
    {
        return $this->users->contains($user) || $user->ownsTeam($this);
    }

    /**
     * Adds a user to the team with a specified role.
     *
     * @param object $user The user model instance to be added to the team.
     * @param string $role_keyword The role ID or code that will be assigned to the user within the team.
     *
     * @return void
     * @throws Exception
     */
    public function addUser(object $user, EnumsRole $role): void
    {
        if ($user->id === $this->owner->id) {
            throw new RuntimeException(__('Owner already belongs to the team.'));
        }

        if ($this->hasUser($user)) {
            throw new RuntimeException(__('User already belongs to the team.'));
        }

        $this->users()->attach($user, ['role_id' => $role->key()]);
    }

    /**
     * Update the role of a specific user within the team.
     *
     * @param object $user The user model instance to be updated in the team.
     * @param string $role_keyword The role ID or code that will be assigned to the user within the team.
     * @return void
     * @throws Exception
     */
    public function updateUser(object $user, EnumsRole $role): void
    {
        if ($user->id === $this->owner->id) {
            throw new RuntimeException(__('You may not change the team owner.'));
        }

        if (!$this->hasUser($user)) {
            throw new RuntimeException(__('User not belongs to the team.'));
        }

        $this->users()->updateExistingPivot($user->id, ['role_id' => $role->key()]);
    }

    /**
     * Remove a user from the team.
     *
     * @param object $user The user instance to remove from the team.
     *
     * @return void
     * @throws Exception
     */
    public function deleteUser(object $user): void
    {
        if ($user->id === $this->owner->id) {
            throw new RuntimeException(__('You may not remove the team owner.'));
        }

        if (!$this->hasUser($user)) {
            throw new RuntimeException(__('User not belongs to the team.'));
        }

        $this->users()->detach($user->id);
    }


    /**
     * Create an invitation and send a message with a link to accept it
     *
     * @param string $email
     * @param int|string $keyword
     * @return Invitation
     * @throws Exception
     */
    public function inviteUser(string $email, EnumsRole $role): Invitation
    {
        if ($this->hasUserWithEmail($email)) {
            throw new RuntimeException(__("This user already belongs to the team."));
        }

        if ($this->hasPendingInvitationForEmail($email)) {
            throw new RuntimeException(__('An invitation has already been sent to this email address.'));
        }

        if ($this->hasReachedMaxInvitationsPerDay($email)) {
            throw new RuntimeException(__("You have reached the maximum number of invitations per day."));
        }

        $invitation = $this->invitations()->create([
            'email'   => $email,
            'token'   => Str::random(64),
            'expires_at' => today()->addDays(15),
            'role_id' => $role->key(),
        ]);

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->notify(new TeamInvitationNotification($invitation));
        }

        return $invitation;
    }

    /**
     * Accept the invitation to the team
     * @param int $invitation_id
     * @return void
     * @throws Exception
     */
    public function inviteAccept(string $token): void
    {
        /** @var Invitation $invitation */
        if (!$invitation = $this->invitations()->where('token', $token)->first()) {
            throw new RuntimeException(__("Invitation not found."));
        }

        if (!$user = $invitation->user) {
            throw new RuntimeException(__("Invited user not found."));
        }

        $this->addUser($user, $invitation->role_enum);

        $this->invitations()->cancelled()->delete();
    }

    /**
     * Check if the team includes a user with a specific email.
     *
     * @param  string  $email
     * @return bool
     */
    public function hasUserWithEmail(string $email): bool
    {
        return $this->allUsers()->contains(fn ($user) => $user->email === $email);
    }

    /**
     * Whether this team already has a non-cancelled, non-accepted invitation for the email.
     */
    public function hasPendingInvitationForEmail(string $email): bool
    {
        return $this->invitations()
            ->whereRaw('LOWER(email) = ?', [Str::lower($email)])
            ->whereNull('cancelled_at')
            ->whereNull('accepted_at')
            ->exists();
    }

    /**
     * Check if the user has reached the maximum number of invitations per day.
     *
     * @param string $email
     * @return bool
     */
    public function hasReachedMaxInvitationsPerDay(string $email): bool
    {
        return $this->invitations()
            ->where('email', $email)
            ->where('created_at', '>=', today()->subDay())
            ->count() > Invitation::MAX_INVITATIONS_PER_DAY;
    }

    /**
     * Check if a user has a specific role in the team.
     *
     * @param object $user
     * @param int|string|null $keyword
     * @return bool
     */
    public function userHasRole(object $user, EnumsRole|int|string|array|null $keyword = null): bool
    {
        return $this->owner === $user ? true : $this->users()
            ->where('user_id', $user->id)
            ->whereHas('roles', function ($query) use ($keyword) {
                $query->when(is_array($keyword), function ($query) use ($keyword) {
                    $query->whereIn('code', $keyword);
                }, function ($query) use ($keyword) {
                    $query->where((is_numeric($keyword) ? 'id' : 'code'), $keyword);
                });
            })
            ->exists();
    }

    /**
     * Purge all the team's resources.
     *
     * @return void
     * @throws Exception
     */
    public function purge(): void
    {
        $this->users()->detach();
        $this->delete();
    }
}
