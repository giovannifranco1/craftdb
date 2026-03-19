<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('area')]
#[Fillable(['diagram_id', 'name', 'x', 'y', 'width', 'height', 'color', 'is_solid'])]
class Area extends Model
{
    /** @use HasFactory<\Database\Factories\AreaFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'x' => 'integer',
            'y' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'is_solid' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Diagram, $this>
     */
    public function diagram(): BelongsTo
    {
        return $this->belongsTo(Diagram::class, 'diagram_id');
    }
}
