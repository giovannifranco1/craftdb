<?php

namespace App\Livewire;

use App\Exceptions\AuthorizationDiagramException;
use App\Models\Diagram;
use App\Concerns\HandlesDiagramAuthAlert;
use App\Exceptions\TableLimitExceededException;
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
    public function exception(Throwable $e, $stopPropagation): void
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
    }

    #[Computed(cache: true, key: 'diagram', seconds: 300)]
    public function diagram(): Diagram
    {
        return Diagram::query()->findOrFail($this->diagramId);
    }

    public function validateTableLimit(): void
    {
        if ($this->diagram->tables()->count() >= config('diagram.table_limit')) {
            throw new TableLimitExceededException($this->diagram->tables()->count());
        }
    }
}
