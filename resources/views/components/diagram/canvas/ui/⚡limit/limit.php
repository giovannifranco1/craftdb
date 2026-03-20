<?php

use App\Models\Diagram;
use App\Support\DiagramLimits;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use App\Livewire\CanvasComponent;

new class extends CanvasComponent {
    /** @var array<string, string> */
    protected $listeners = [
        'table::added' => '$refresh',
        // 'table::removed' => '$refresh',
    ];

    public int $limit;

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;
        $owner = $diagram->team?->owner;
        abort_if($owner === null, 403, 'Team owner not found');
        $limits = DiagramLimits::forUser($owner);
        $this->limit = $limits['table_limit'];
    }

    #[Computed()]
    public function tableCount(): int
    {
        return $this->diagram->tables()->count();
    }
};
