<?php

use App\Attributes\AuthorizeDiagram;
use App\Concerns\HandlesDiagramAuthAlert;
use App\Models\Diagram;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use App\Livewire\BaseComponent;

new #[Defer(bundle: true)] class extends BaseComponent {
    use HandlesDiagramAuthAlert;

    #[Locked]
    public int $diagramId;

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

    #[Computed]
    public function diagram(): Diagram
    {
        return Diagram::query()->findOrFail($this->diagramId);
    }

    /**
     * @return Collection<int, \App\Models\Area>
     */
    #[Computed]
    public function getAreas(): Collection
    {
        return $this->diagram->areas()->get();
    }

    #[On('area::add')]
    public function addArea(int $x = 100, int $y = 100): void
    {
        $this->authorizeDiagram('update');

        $area = $this->diagram->areas()->create([
            'name' => 'New Area',
            'x' => $x,
            'y' => $y,
            'width' => 400,
            'height' => 300,
            'color' => Helpers::getRandomColor($this->diagram->style),
        ]);

        $count = $this->diagram->areas()->count();

        $this->dispatch('alert', message: "Area $count added successfully.");
        $this->dispatch('area::added', areaId: $area->id);
    }
};
