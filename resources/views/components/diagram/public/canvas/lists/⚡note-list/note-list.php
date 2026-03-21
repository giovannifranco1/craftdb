<?php

use App\Models\Diagram;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use App\Livewire\CanvasComponent;
use App\Livewire\Contracts\PublicCanvas;

new #[Defer(bundle: true)] class extends CanvasComponent implements PublicCanvas {

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

    /** @return Collection<int, \App\Models\Note> */
    #[Computed]
    public function notes(): Collection
    {
        return $this->diagram->notes()->get();
    }
};
