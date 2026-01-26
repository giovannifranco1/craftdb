<?php

use App\Livewire\CanvasComponent;
use App\Livewire\Contracts\PublicCanvas;
use App\Models\Diagram;
use App\Support\Helpers;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Defer;

new #[Defer(bundle: true)] class extends CanvasComponent implements PublicCanvas {
    /** @var array<string, string>  */
    protected $listeners = [
        'table::color-updated'                => '$refresh',
        'table::column-added'                 => '$refresh',
        'table::removed'                      => '$refresh',
        'table-side-editor::column-saved'     => '$refresh',
        'table-side-editor::column-deleted'   => '$refresh',
        'table-side-editor::column-updated'   => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;

        $offsets = Helpers::getPositionCanvasByDiagram($diagram);

        $this->dispatch('table-list::inicializeted', ...$offsets);
    }

    /**
     * @return Collection<int, \App\Models\Table>
     */
    #[Computed]
    public function tables(): Collection
    {
        $tables = $this->diagram->tables();

        $tables = $tables->withForeignCount();
        $tables = $tables->withPrimaryCount();
        $tables = $tables->withColumnsCount();

        return $tables
            ->withExists('columns')
            ->with(['diagram'])
            ->with(['columns' => fn(QueryBuilder $query): QueryBuilder => $query->orderBy('order')])
            ->with(['foreignKeys' => fn(QueryBuilder $query): QueryBuilder => $query->orderByDesc('order')])
            ->get();
    }
};
