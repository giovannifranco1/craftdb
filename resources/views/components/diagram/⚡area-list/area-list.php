<?php

use App\Concerns\HandlesDiagramAuthorizationWithAlertError;
use App\Models\Diagram;
use App\Support\Helpers;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use HandlesDiagramAuthorizationWithAlertError;

    #[Locked]
    public Diagram $diagram;

    /** @var array<string, string>  */
    protected $listeners = [
        'area::removed' => '$refresh',
        'area::color-updated' => '$refresh',
        'area::added' => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
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
        if (!$this->authorizeDiagramWithAlert('update', $this->diagram)) {
            return;
        }

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
