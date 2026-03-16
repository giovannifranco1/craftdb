<?php

use App\Attributes\AuthorizeDiagram;
use App\Concerns\HandlesDiagramAuthAlert;
use App\Exceptions\PlanFeatureException;
use App\Models\Diagram;
use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\On;
use App\Livewire\CanvasComponent;

new #[Defer(bundle: true)] class extends CanvasComponent {

    /** @var array<string, string>  */
    protected $listeners = [
        'note::removed' => '$refresh',
        'note::color-updated' => '$refresh',
        'note::added' => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;
    }

    #[Computed]
    public function notes(): Collection
    {
        return $this->diagram->notes()->get();
    }

    #[On('note::add')]
    public function addNote(int $x = 100, int $y = 100): void
    {
        $this->authorizeDiagram('update');

        if (! Auth::user()->isPro()) {
            throw new PlanFeatureException(__('Sticky notes are available on the Pro plan or higher.'));
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

        $this->alert(__('Note :count added successfully.', ['count' => $count]));
        $this->dispatch('note::added', noteId: $note->id);
    }
};
