<?php

namespace App\Models;

use App\Exceptions\DiagramLimitExceededException;
use App\Models\Concerns\HasTeams;
use App\Models\Team\Membership;
use App\Models\Team\Team;
use App\Support\DiagramLimits;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_dark
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Team> $ownedTeams
 * @property-read int|null $owned_teams_count
 * @property-read Membership|null $membership
 * @property-read Collection<int, Team> $teams
 * @property-read int|null $teams_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'email', 'password', 'locale', 'is_dark'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasApiTokens, HasFactory, HasTeams, Notifiable;

    /**
     * Default attribute values so strict mode never sees missing keys after insert
     * (DB defaults are not always merged back onto the in-memory model).
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'locale' => 'en',
        'is_dark' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_dark' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    private static array $isProCache = [];

    private static array $isTeamPlanCache = [];

    /**
     * Reset subscription lookups (tests reuse user ids after RefreshDatabase; ??= would keep stale booleans).
     */
    public static function clearSubscriptionCaches(): void
    {
        static::$isProCache = [];
        static::$isTeamPlanCache = [];
    }

    public function isPro(): bool
    {
        return static::$isProCache[$this->id] ??= $this->subscribed(
            config('billing.subscription_name', 'default')
        );
    }

    public function isTeamPlan(): bool
    {
        return static::$isTeamPlanCache[$this->id] ??= (function () {
            $prices = array_filter([
                config('billing.plans.team_usd.price_id'),
                config('billing.plans.team_brl.price_id'),
                config('billing.plans.team_usd_annual.price_id'),
                config('billing.plans.team_brl_annual.price_id'),
            ]);

            foreach ($prices as $priceId) {
                if ($this->subscribedToPrice($priceId, config('billing.subscription_name', 'default'))) {
                    return true;
                }
            }

            return false;
        })();
    }

    /**
     * Pro/Team features in a workspace: own subscription, or team owner paid (invited members inherit).
     */
    public function hasTeamWorkspacePremiumAccess(?Team $team = null): bool
    {
        if ($this->isPro() || $this->isTeamPlan()) {
            return true;
        }

        $team ??= Team::currentTeam();

        if (! $team) {
            return false;
        }

        $team->loadMissing(['owner', 'users']);

        if (! $team->hasUser($this)) {
            return false;
        }

        $owner = $team->owner;
        if (! $owner) {
            return false;
        }

        return $owner->isPro() || $owner->isTeamPlan();
    }

    /**
     * Pro/Team features on a diagram: own subscription, or team owner paid (invited members inherit).
     */
    public function hasDiagramPremiumAccess(?Diagram $diagram = null): bool
    {
        if ($diagram === null) {
            return false;
        }

        $diagram->loadMissing('team');

        return $this->hasTeamWorkspacePremiumAccess($diagram->team);
    }

    public function canCreateTeam(): bool
    {
        return $this->isTeamPlan();
    }

    public function hasReachedDiagramLimit(?Team $team = null): bool
    {
        $team ??= Team::currentTeam();

        if (! $team) {
            return false;
        }

        $owner = $team->owner;
        if (! $owner) {
            return false;
        }

        $limits = DiagramLimits::forUser($owner);

        return $team->diagrams()->count() >= $limits['diagram_limit'];
    }

    public function validateDiagramLimit(): void
    {
        if ($this->hasReachedDiagramLimit()) {
            throw new DiagramLimitExceededException;
        }
    }
}
