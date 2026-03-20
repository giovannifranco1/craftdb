<?php

namespace App\Livewire;

use App\Exceptions\AuthorizationDiagramException;
use App\Models\Diagram;
use App\Concerns\HandlesDiagramAuthAlert;
use App\Exceptions\PlanFeatureException;
use App\Exceptions\TableColumnLimitExceededException;
use App\Exceptions\TableLimitExceededException;
use App\Support\DiagramLimits;
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

        if ($e instanceof TableLimitExceededException) {
            $stopPropagation();
            $this->alert(message: $e->getMessage(), type: 'error');
        }

        if ($e instanceof TableColumnLimitExceededException) {
            $stopPropagation();
            $this->alert(message: $e->getMessage(), type: 'error');
        }

        if ($e instanceof PlanFeatureException) {
            $stopPropagation();
            $this->alert(message: $e->getMessage(), type: 'error');
        }
    }

    #[Computed]
    public function diagram(): Diagram
    {
        return Cache::store('array')->rememberForever(
            "diagram:{$this->diagramId}",
            fn() => Diagram::query()->with(['team.owner'])->findOrFail($this->diagramId)
        );
    }

    public function validateTableLimit(): void
    {
        $owner = $this->diagram->team?->owner;
        abort_if($owner === null, 403, 'Team owner not found');
        $limits = DiagramLimits::forUser($owner);

        if ($this->diagram->tables()->count() >= $limits['table_limit']) {
            throw new TableLimitExceededException($this->diagram->tables()->count());
        }
    }
}
