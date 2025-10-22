<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagram extends Model
{
    use HasFactory;

    protected $table = 'diagram';

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'diagram_id');
    }
}
