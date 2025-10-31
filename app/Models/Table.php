<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    const COLOR_DEFAULT = '#2563eb';
    
    use HasFactory;

    protected $table = 'table';

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class, 'table_id');
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
}
