<?php

namespace App\Livewire\Diagram;

use App\Models\Area as AreaModel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class Area extends Component
{
    /** @var array<string, string>  */
    protected $listeners = [
        'area::color-updated' => '$refresh',
        'area::renamed' => '$refresh',
    ];

    #[Locked]
    public AreaModel $area;

    #[Locked]
    public int $diagramId;

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
        $this->area->x = $x;
        $this->area->y = $y;
        $this->area->save();
    }

    #[On('area::resize.{area.id}')]
    public function updateSize(int $x, int $y, int $width, int $height): void
    {
        $this->area->width = $width;
        $this->area->height = $height;
        $this->area->x = $x;
        $this->area->y = $y;
        $this->area->save();
    }

    public function renameArea(): void
    {
        $this->area->name = $this->name;
        $this->area->save();

        $this->dispatch('area::renamed');
    }

    public function resetName(): void
    {
        $this->name = $this->area->name;
    }

    public function setColor(string $color): void
    {
        $this->area->color = $color;

        if (!in_array($color, array_merge(config('color.colorful'), config('color.neutral')))) {
            return;
        }

        $this->area->save();
        $this->area = $this->area->fresh();

        $this->dispatch('alert', message: "Color Updated");
        $this->dispatch('area::color-updated');
    }

    public function remove(): void
    {
        $this->area->delete();

        $this->dispatch('alert', message: "Area Removed");
        $this->dispatch('area::removed');
    }

    public function render(): View
    {
        return view('livewire.diagram.area');
    }
}
