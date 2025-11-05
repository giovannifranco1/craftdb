<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    use HasFactory;

    protected $table = 'column';

    public function relationshipsFrom(): HasMany
    {
        return $this->hasMany(Relationship::class, 'from_column_id');
    }

    public function relationshipsTo(): HasMany
    {
        return $this->hasMany(Relationship::class, 'to_column_id');
    }

    public function getAllRelationshipsAttribute()
    {
        return $this->relationshipsFrom->merge($this->relationshipsTo);
    }

    public function scopeFilterByForeignKeys($query)
    {
        return $query->where('is_foreign', true);
    }
}
