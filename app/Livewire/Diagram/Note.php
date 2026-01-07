<?php

namespace App\Livewire\Diagram;

use App\Models\Note as NoteModel;
use App\Models\Team\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Note extends Component
{
    /** @var array<string, string>  */
    protected $listeners = [
        'note::color-updated' => '$refresh',
        'note::renamed' => '$refresh',
    ];

    #[Locked]
    public NoteModel $note;

    #[Locked]
    public int $diagramId;

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
        $this->note->x = $x;
        $this->note->y = $y;
        $this->note->save();
    }

    #[On('note::resize.{note.id}')]
    public function updateSize(int $x, int $y, int $width, int $height): void
    {
        $this->note->width = $width;
        $this->note->height = $height;
        $this->note->x = $x;
        $this->note->y = $y;
        $this->note->save();
    }

    public function renameNote(): void
    {
        if ($this->note->diagram->team_id != Team::currentTeam()->getKey()) {
            abort(403, 'Unauthorized access to diagram');
        }

        $this->note->name = $this->name;
        $this->note->save();

        $this->dispatch('note::renamed');
    }

    public function resetName(): void
    {
        $this->name = $this->note->name;
    }

    public function updateContent(): void
    {
        if (!$this->note->diagram->team_id == Team::currentTeam()->getKey()) {
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
        $this->note->delete();

        $this->dispatch('alert', message: "Note Removed");
        $this->dispatch('note::removed');
    }

    public function render(): View
    {
        return view('livewire.diagram.note');
    }
}
