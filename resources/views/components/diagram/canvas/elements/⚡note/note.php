<?php

use App\Models\Note as NoteModel;
use App\Models\Team\Team;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use App\Livewire\CanvasComponent;

new class extends CanvasComponent {

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

    #[On('note::move.{note.id}')]
    public function updatePosition(int $x, int $y): void
    {
        $this->authorizeDiagram('update');

        $this->note->x = $x;
        $this->note->y = $y;
        $this->note->save();
    }

    #[On('note::resize.{note.id}')]
    public function updateSize(int $x, int $y, int $width, int $height): void
    {
        $this->authorizeDiagram('update');

        $this->note->width = $width;
        $this->note->height = $height;
        $this->note->x = $x;
        $this->note->y = $y;
        $this->note->save();
    }

    public function renameNote(): void
    {
        $this->authorizeDiagram('update');

        $this->note->name = $this->name;
        $this->note->save();

        $this->dispatch('note::renamed');
    }

    public function resetName(): void
    {
        $this->authorizeDiagram('update');

        $this->name = $this->note->name;
    }

    public function updateContent(): void
    {
        $this->authorizeDiagram('update');

        $diagram = $this->note->diagram;

        $currentTeam = Team::currentTeam();
        if (!$currentTeam || $diagram?->team_id !== $currentTeam->getKey()) {
            abort(403, 'Unauthorized access to diagram');
        }

        $this->note->content = $this->content;
        $this->note->save();

        $this->dispatch('note::content-updated');
    }

    public function updatedContent(): void
    {
        $this->updateContent();
    }

    public function remove(): void
    {
        $this->authorizeDiagram('update');

        $this->note->delete();

        $this->dispatch('alert', message: "Note Removed");
        $this->dispatch('note::removed');
    }
};
