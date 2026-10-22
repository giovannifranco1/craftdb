<?php

namespace App\Livewire;

use App\Exceptions\AuthorizationDiagramException;
use App\Models\Diagram;
use App\Concerns\HandlesDiagramAuthAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Override;
use Throwable;

abstract class CanvasComponent extends BaseComponent
{
    use HandlesDiagramAuthAlert;

    #[Locked]
    public int $diagramId;

    #[Override]
    public function exception(Throwable $e, \Closure $stopPropagation): void
    {
        if ($e instanceof AuthorizationDiagramException) {
            $stopPropagation();
            $this->alert(message: __('You are not authorized to perform this action on this diagram.'), type: 'error');
        }

        if ($e instanceof ValidationException) {
            $stopPropagation();
            $this->alert(message: $e->getMessage(), type: 'error');
        }
    }

    #[Computed]
    public function diagram(): Diagram
    {
        return Cache::store('array')->rememberForever(
            "diagram:{$this->diagramId}",
            fn() => Diagram::query()->with('team')->findOrFail($this->diagramId)
        );
    }
}
