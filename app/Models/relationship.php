<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    use HasFactory;

    protected $table = 'relationship';

    public function fromTable()
    {
        return $this->belongsTo(Table::class, 'from_table_id');
    }

    public function toTable()
    {
        return $this->belongsTo(Table::class, 'to_table_id');
    }

    public function fromColumn()
    {
        return $this->belongsTo(Column::class, 'from_column_id');
    }

    public function toColumn()
    {
        return $this->belongsTo(Column::class, 'to_column_id');
    }
}
