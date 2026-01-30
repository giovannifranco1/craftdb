<?php

namespace App\Models;

use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;

    const DEFAULT_COLOR = '#fef08a';

    protected $table = 'note';

    protected $fillable = [
        'diagram_id',
        'name',
        'content',
        'x',
        'y',
        'width',
        'height',
        'color',
    ];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * @return BelongsTo<Diagram, $this>
     */
    public function diagram(): BelongsTo
    {
        return $this->belongsTo(Diagram::class, 'diagram_id');
    }
}
