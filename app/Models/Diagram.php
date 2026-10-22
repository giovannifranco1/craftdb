<?php

namespace App\Models;

use App\Models\Team\Team;
use App\Observers\DiagramObserver;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table as TableAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;

/**
 * @property int $id
 * @property int $team_id
 * @property string $name
 * @property string $slug
 * @property string $style
 * @property int $is_initial_load
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Table> $tables
 * @property-read int|null $tables_count
 * @property-read array<int, string> $colors
 * @method static \Database\Factories\DiagramFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereIsInitialLoad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Diagram findBySlug($value)
 * @mixin \Eloquent
 */
#[ObservedBy(DiagramObserver::class)]
#[TableAttribute('diagram')]
#[Appends(['colors'])]
class Diagram extends Model
{
    /** @use HasFactory<\Database\Factories\DiagramFactory> */
    use HasFactory;

    /**
     * @return HasMany<\App\Models\Table, $this>
     */
    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'diagram_id');
    }

    /**
     * @return HasMany<\App\Models\Area, $this>
     */
    public function areas(): HasMany
    {
        return $this->hasMany(Area::class, 'diagram_id');
    }

    /**
     * @return HasMany<\App\Models\Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'diagram_id');
    }

    /**
     * @return Builder<Relationship>
     */
    public function relationships()
    {
        return Relationship::query()
            ->whereHas('fromTable', fn($q) => $q->withoutGlobalScope(\App\Models\Scopes\TableAccessible::class)->where('diagram_id', $this->id))
            ->orWhereHas('toTable', fn($q) => $q->withoutGlobalScope(\App\Models\Scopes\TableAccessible::class)->where('diagram_id', $this->id));
    }

    /**
     * @return Attribute<list<string>, never>
     */
    public function colors(): Attribute
    {
        return new Attribute(
            get: function (): array {
                $colors = match ($this->style) {
                    'colorful' => config('color.colorful'),
                    'neutral'  => config('color.neutral'),
                    default    => [],
                };

                $colors = Arr::wrap($colors);

                return array_values(array_filter(
                    $colors,
                    static fn($color): bool => is_string($color)
                ));
            }
        );
    }

    /**
     * @param Builder<$this> $query
     * @param string $slug
     * @return Builder<$this>
     */
    #[Scope]
    public function findBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }

    public function isFavorite(): bool
    {
        return $this->is_favorite;
    }

    public function getTeamId(): int
    {
        return $this->team_id;
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    /**
     * @return BelongsToMany<User, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'diagram_views', 'diagram_id', 'user_id')
            ->withPivot('last_viewed_at')
            ->withTimestamps()
            ->orderByPivot('last_viewed_at', 'desc');
    }

    /**
     * Registra que o usuário visualizou o diagrama
     */
    public function recordView(User $user): void
    {
        $this->viewers()->syncWithoutDetaching([
            $user->id => ['last_viewed_at' => now()]
        ]);
    }
}
