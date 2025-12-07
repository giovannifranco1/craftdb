<?php

namespace App\Livewire\Diagram;

use App\Models\Diagram;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class NoteList extends Component
{
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
        $note = $this->diagram->notes()->create([
            'name' => 'New Note',
            'content' => '',
            'x' => $x,
            'y' => $y,
            'width' => 300,
            'height' => 200,
            'color' => '#fef08a', // Default yellow post-it color
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
