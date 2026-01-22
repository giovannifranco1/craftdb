<?php

use App\Attributes\AuthorizeDiagram;
use App\Concerns\HandlesDiagramAuthAlert;
use App\Models\Area as AreaModel;
use App\Models\Diagram;
use App\Support\Helpers;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Livewire\BaseComponent;

new class extends BaseComponent {
    use HandlesDiagramAuthAlert;

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

    #[Computed(cache: true, key: 'diagram')]
    public function diagram(): Diagram
    {
        return Diagram::query()->findOrFail($this->diagramId);
    }

    #[On('area::move.{area.id}')]
    public function updatePosition(int $x, int $y): void
    {
        $this->area->update([
            'x' => $x,
            'y' => $y,
        ]);
    }

    #[On('area::resize.{area.id}')]
    public function updateSize(int $x, int $y, int $width, int $height): void
    {
        $this->area->update([
            'width' => $width,
            'height' => $height,
            'x' => $x,
            'y' => $y,
        ]);
    }

    public function renameArea(): void
    {
        $this->validateOnly('name');

        $this->area->update(['name' => $this->name]);

        $this->dispatch('area::renamed');
    }

    public function resetName(): void
    {
        $this->name = $this->area->name;
    }

    public function setColor(string $color): void
    {
        if (!Helpers::isColorValid($color)) return;

        $this->area->update(['color' => $color]);

        $this->dispatch('area::color-updated');
    }

    public function remove(): void
    {
        $this->area->delete();

        $this->dispatch('alert', message: "Area Removed");
        $this->dispatch('area::removed');
    }
};
