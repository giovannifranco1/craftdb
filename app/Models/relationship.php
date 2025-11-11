<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $from_table_id
 * @property int $from_column_id
 * @property int $to_table_id
 * @property int $to_column_id
 * @property string $type
 * @property string|null $label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Column $fromColumn
 * @property-read \App\Models\Table $fromTable
 * @property-read \App\Models\Column $toColumn
 * @property-read \App\Models\Table $toTable
 * @method static \Database\Factories\RelationshipFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereFromColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereFromTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereToColumnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereToTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Relationship whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Relationship extends Model
{
    /** @use HasFactory<\Database\Factories\RelationshipFactory> */
    use HasFactory;

    protected $table = 'relationship';

    /**
     * @return BelongsTo<Table, $this>
     */
    public function fromTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'from_table_id');
    }

    /**
     * @return BelongsTo<Table, $this>
     */
    public function toTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'to_table_id');
    }

    /**
     * @return BelongsTo<Column, $this>
     */
    public function fromColumn(): BelongsTo
    {
        return $this->belongsTo(Column::class, 'from_column_id');
    }

    /**
     * @return BelongsTo<Column, $this>
     */
    public function toColumn(): BelongsTo
    {
        return $this->belongsTo(Column::class, 'to_column_id');
    }
}
