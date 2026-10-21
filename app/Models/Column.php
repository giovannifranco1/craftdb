<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table as TableAttribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int $table_id
 * @property string $name
 * @property string $type
 * @property int $is_primary
 * @property int $is_foreign
 * @property int $is_notnull
 * @property int $order
 * @property int|null $length
 * @property string|null $default_value
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool|null $relationships_from_exists
 * @property-read bool|null $relationships_to_exists
 * @property-read Collection<int, Relationship> $all_relationships
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Relationship> $relationshipsFrom
 * @property-read int|null $relationships_from_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Relationship> $relationshipsTo
 * @property-read int|null $relationships_to_count
 * @method static \Database\Factories\ColumnFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column filterByForeignKeys()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereIsForeign($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereIsNotnull($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Column whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[TableAttribute('column')]
class Column extends Model
{
    /** @use HasFactory<\Database\Factories\ColumnFactory> */
    use HasFactory;

    /**
     * @return HasMany<Relationship, $this>
     */
    public function relationshipsFrom(): HasMany
    {
        return $this->hasMany(Relationship::class, 'from_column_id');
    }

    /**
     * @return HasMany<Relationship, $this>
     */
    public function relationshipsTo(): HasMany
    {
        return $this->hasMany(Relationship::class, 'to_column_id');
    }

    /**
     * @return BelongsTo<Table, $this>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function allRelationships(): Attribute
    {
        return Attribute::make(
            get: fn (): Collection => $this->relationshipsFrom->merge($this->relationshipsTo)
        );
    }

    /**
     * @param Builder<Column> $query
     * @return Builder<Column>
     */
    #[Scope]
    public function filterByForeignKeys(Builder $query): Builder
    {
        return $query->where('is_foreign', true);
    }

    /**
     * Scope para filtrar apenas colunas que o usuário tem acesso
     *
     * @param Builder<Column> $query
     * @param User|null $user
     * @return Builder<Column>
     */
    #[Scope]
    public function accessible(Builder $query, ?User $user = null): Builder
    {
        if ($user === null) {
            /** @var User|null $authenticatedUser */
            $authenticatedUser = Auth::user();
            $user = $authenticatedUser;
        }

        if (!$user) {
            return $query->whereRaw('1 = 0'); // Retorna vazio se não houver usuário
        }

        return $query->whereHas('table.diagram', function (Builder $q) use ($user): void {
            $q->whereIn('team_id', $user->ownedTeams()->pluck('team.id'));
        });
    }
}
