<?php

use App\Livewire\CanvasComponent;
use App\Models\Diagram;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;

new class extends CanvasComponent {
    #[Locked]
    public int $diagramId;

    public string $framework = 'laravel';

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;
    }

    #[Computed]
    public function canExport(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user && $user->hasDiagramPremiumAccess($this->diagram);
    }

    public function export(): mixed
    {
        $this->authorizeDiagram('view');

        if (! $this->canExport) {
            $this->alert(__('Export is available on the Pro plan or higher.'), type: 'error');
            return null;
        }

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
