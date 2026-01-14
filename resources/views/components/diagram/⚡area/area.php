<?php

use App\Concerns\HandlesDiagramAuthorizationWithAlertError;
use App\Models\Area as AreaModel;
use App\Support\Helpers;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    use HandlesDiagramAuthorizationWithAlertError;

    /** @var array<string, string>  */
    protected $listeners = [
        'area::color-updated' => '$refresh',
        'area::renamed' => '$refresh',
    ];

    #[Locked]
    public AreaModel $area;

    #[Locked]
    public int $diagramId;

    #[Validate('nullable|string|max:255')]
    public ?string $name = null;

    public function mount(AreaModel $area): void
    {
        $this->area = $area;
        $this->diagramId = $area->diagram_id;
        $this->name = $area->name;
    }

    #[On('area::move.{area.id}')]
    public function updatePosition(int $x, int $y): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->area->diagram)) {
            return;
        }

        $this->area->x = $x;
        $this->area->y = $y;
        $this->area->save();
    }

    #[On('area::resize.{area.id}')]
    public function updateSize(int $x, int $y, int $width, int $height): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->area->diagram)) {
            return;
        }

        $this->area->width = $width;
        $this->area->height = $height;
        $this->area->x = $x;
        $this->area->y = $y;
        $this->area->save();
    }

    public function renameArea(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->area->diagram)) {
            return;
        }

        $this->validateOnly('name');

        $this->area->name = $this->name;
        $this->area->save();

        $this->dispatch('area::renamed');
    }

    public function resetName(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->area->diagram)) {
            return;
        }

        $this->name = $this->area->name;
    }

    public function setColor(string $color): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->area->diagram)) {
            $this->dispatch('area::color-updated-error');
            return;
        }

        if (!Helpers::isColorValid($color)) return;

        $this->area->color = $color;
        $this->area->save();

        $this->dispatch('area::color-updated');
    }

    public function remove(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->area->diagram)) {
            return;
        }

        $this->area->delete();

        $this->dispatch('alert', message: "Area Removed");
        $this->dispatch('area::removed');
    }
};
