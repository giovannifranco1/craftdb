<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Relationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;
use InvalidArgumentException;

/**
 * @property int $id
 * @property int $diagram_id
 * @property string $name
 * @property int $x
 * @property int $y
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Column> $columns
 * @property-read int|null $columns_count
 * @property-read \App\Models\Diagram $diagram
 * @method static \Database\Factories\TableFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereDiagramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereX($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereY($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table withForeignCount()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table withPrimaryCount()
 * @mixin \Eloquent
 */
class Table extends Model
{
    const COLOR_DEFAULT = '#2563eb';

    /** @use HasFactory<\Database\Factories\TableFactory> */
    use HasFactory;

    protected $table = 'table';

    /**
     * @return HasMany<Column, $this>
     */
    public function columns(): HasMany
    {
        return $this->hasMany(Column::class, 'table_id');
    }

    /**
     * @return BelongsTo<Diagram, $this>
     */
    public function diagram(): BelongsTo
    {
        return $this->belongsTo(Diagram::class, 'diagram_id');
    }

       /**
     * @param  Builder<$this> $query
     * @return Builder<$this>
     */
    public function scopeWithPrimaryCount(Builder $query): Builder
    {
        return $query->withCount([
            'columns as primary_count' => function ($q) {
                $q->where('is_primary', true);
            },
        ]);
    }

    /**
     * @param  Builder<$this> $query
     * @return Builder<$this>
     */
    public function scopeWithForeignCount(Builder $query): Builder
    {
        return $query->withCount([
            'columns as foreign_count' => function ($q) {
                $q->where('is_foreign', true);
            },
        ]);
    }

    /**
     * @return Builder<\App\Models\Relationship>
     */
    public function relationships()
    {
        return Relationship::query()->where(function ($query) {
                $query->where('from_table_id', $this->id)
                    ->orWhere('to_table_id', $this->id);
            })
            ->with(['fromTable', 'toTable', 'fromColumn', 'toColumn']);
    }
}
