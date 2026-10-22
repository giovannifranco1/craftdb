<?php

use App\Livewire\CanvasComponent;
use App\Models\Diagram;
use Flux\Flux;
use Livewire\Attributes\Locked;

new class extends CanvasComponent {
    #[Locked]
    public int $diagramId;

    public string $framework = 'laravel';

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;
    }

    public function export(): mixed
    {
        $this->authorizeDiagram('view');

        Flux::modal('diagram-canvas-export')->close();

        $diagram = $this->diagram;
        $teamId = $diagram->team_id;

        return $this->redirect(
            route('diagram.export-laravel-migration', [$teamId, $diagram->slug]),
            navigate: false
        );
    }

    public function cancel(): void
    {
        $this->framework = 'laravel';
        Flux::modal('diagram-canvas-export')->close();
    }
};
