<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Relationship;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Table extends Model
{
    const COLOR_DEFAULT = '#2563eb';

    use HasFactory;

    protected $table = 'table';

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class, 'table_id');
    }

    public function diagram(): BelongsTo
    {
        return $this->belongsTo(Diagram::class, 'diagram_id');
    }

    public function scopeWithPrimaryCount($query)
    {
        $query->withCount([
            'columns as primary_count' => function ($q) {
                $q->where('is_primary', true);
            },
        ]);
    }

    public function scopeWithForeignCount($query)
    {
        $query->withCount([
            'columns as foreign_count' => function ($q) {
                $q->where('is_foreign', true);
            },
        ]);
    }

    public function relationships()
    {
        return Relationship::query()
            ->where(function ($query) {
                $query->where('from_table_id', $this->id)
                    ->orWhere('to_table_id', $this->id);
            })
            ->with(['fromTable', 'toTable', 'fromColumn', 'toColumn']);
    }
}
