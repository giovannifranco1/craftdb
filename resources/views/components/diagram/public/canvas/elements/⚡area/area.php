<?php

use App\Livewire\CanvasComponent;
use App\Models\Area as AreaModel;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use App\Livewire\Contracts\PublicCanvas;

new class extends CanvasComponent implements PublicCanvas {

    /** @var array<string, string>  */
    protected $listeners = [
        'area::color-updated' => '$refresh',
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
};
