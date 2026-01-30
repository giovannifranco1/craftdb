<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    /** @use HasFactory<\Database\Factories\AreaFactory> */
    use HasFactory;

    protected $table = 'area';

    protected $fillable = [
        'diagram_id',
        'name',
        'x',
        'y',
        'width',
        'height',
        'color',
        'is_solid',
    ];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_solid' => 'boolean',
    ];

    /**
     * @return BelongsTo<Diagram, $this>
     */
    public function diagram(): BelongsTo
    {
        return $this->belongsTo(Diagram::class, 'diagram_id');
    }
}
