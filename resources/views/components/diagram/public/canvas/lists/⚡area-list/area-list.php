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
        'area::removed' => '$refresh',
        'area::color-updated' => '$refresh',
        'area::added' => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;
    }

    /**
     * @return Collection<int, \App\Models\Area>
     */
    #[Computed]
    public function getAreas(): Collection
    {
        return $this->diagram->areas()->get();
    }
};
