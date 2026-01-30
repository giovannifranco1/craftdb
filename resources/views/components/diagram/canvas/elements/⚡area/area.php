<?php

use App\Livewire\CanvasComponent;
use App\Models\Area as AreaModel;
use App\Support\Helpers;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Validate;

new class extends CanvasComponent {

    /** @var array<string, string>  */
    protected $listeners = [
        // 'area::color-updated' => '$refresh',
        'area::renamed' => '$refresh',
    ];

    #[Locked]
    public AreaModel $area;

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
        $this->area->update([
            'x' => $x,
            'y' => $y,
        ]);
        $this->area->refresh();
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
        $this->area->refresh();
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

    public function toggleSolid(): void
    {
        $this->area->update(['is_solid' => !$this->area->is_solid]);

        $this->dispatch('area::solid-updated');
    }

    public function remove(): void
    {
        $this->area->delete();

        $this->dispatch('alert', message: "Area Removed");
        $this->dispatch('area::removed');
    }
};
