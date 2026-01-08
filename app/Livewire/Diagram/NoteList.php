<?php

namespace App\Livewire\Diagram;

use App\Concerns\HandlesDiagramAuthorizationWithAlertError;
use App\Models\Diagram;
use App\Models\Note;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class NoteList extends Component
{
    use HandlesDiagramAuthorizationWithAlertError;

    #[Locked]
    public Diagram $diagram;

    /** @var array<string, string>  */
    protected $listeners = [
        'note::removed' => '$refresh',
        'note::color-updated' => '$refresh',
        'note::added' => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
    }

    /**
     * @return Collection<int, \App\Models\Note>
     */
    #[Computed(persist: true)]
    public function getNotes(): Collection
    {
        return $this->diagram->notes()->get();
    }

    #[On('note::add')]
    public function addNote(int $x = 100, int $y = 100): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->diagram)) {
            return;
        }

        $note = $this->diagram->notes()->create([
            'name' => 'New Note',
            'content' => '',
            'x' => $x,
            'y' => $y,
            'width' => 175,
            'height' => 75,
            'color' => Note::DEFAULT_COLOR,
        ]);

        $count = $this->diagram->notes()->count();

        $this->dispatch('alert', message: "Note $count added successfully.");
        $this->dispatch('note::added', noteId: $note->id);
    }

    public function render(): View
    {
        return view('livewire.diagram.note-list');
    }
}
