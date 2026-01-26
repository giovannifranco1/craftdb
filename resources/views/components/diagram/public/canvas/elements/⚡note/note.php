<?php

use App\Models\Note as NoteModel;
use Livewire\Attributes\Locked;
use App\Livewire\CanvasComponent;
use App\Livewire\Contracts\PublicCanvas;

new class extends CanvasComponent implements PublicCanvas {

    /** @var array<string, string>  */
    protected $listeners = [
        'note::color-updated' => '$refresh',
        'note::renamed' => '$refresh',
    ];

    #[Locked]
    public NoteModel $note;

    public ?string $name = null;
    public ?string $content = null;

    public function mount(NoteModel $note): void
    {
        $this->note = $note;
        $this->diagramId = $note->diagram_id;
        $this->name = $note->name;
        $this->content = $note->content;
    }
};
