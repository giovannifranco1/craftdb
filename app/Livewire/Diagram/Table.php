<?php

namespace App\Livewire\Diagram;

use App\Concerns\HandlesDiagramAuthorizationWithAlertError;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Table as TableModel;
use App\Support\Helpers;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Table extends Component
{
    use HandlesDiagramAuthorizationWithAlertError;

    /** @var array<string, string>  */
    protected $listeners = [
        'table::column-saved' => '$refresh',
        'table-side-editor::column-added'   => '$refresh',
        'table-side-editor::column-saved'   => '$refresh',
        'table-side-editor::column-deleted' => '$refresh',
        'table-side-editor::column-updated' => '$refresh',
    ];

    #[Locked]
    public TableModel $table;

    #[Locked]
    public int $diagramId;

    #[Validate('required|string|max:255')]
    public ?string $name = null;

    /** @var array<string, string>  */
    public $relationship = [
        'from_column_id' => '',
        'to_column_id'   => '',
        'type'           => '',
    ];

    public function mount(TableModel $table): void
    {
        $this->table = $table;
        $this->diagramId = $table->diagram_id;
        $this->name = $table->name;
    }

    #[Computed(persist: true)]
    public function getColumns(): Collection
    {
        return $this->table->columns()->orderBy('order')->get();
    }

    public function renameTable(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        $this->validateOnly('name');

        $this->table->name = $this->name;
        $this->table->save();

        $this->dispatch('table::renamed');
        $this->refreshTableWithCounts();
    }

    public function resetName(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        $this->name = $this->table->name;
        $this->refreshTableWithCounts();
    }

    public function setColor(string $color): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            $this->dispatch('table::color-updated-error');
            return;
        }

        if (!Helpers::isColorValid($color)) return;

        $this->table->color = $color;
        $this->table->save();

        $this->refreshTableWithCounts();
        $this->dispatch('table::color-updated');
    }

    public function renameColumn(Column $column, $newName): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        $column->name = $newName;
        $column->save();

        $this->refreshTableWithCounts();
        $this->dispatch('table::column-renamed');
    }

    public function addColumn(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        $maxOrder = $this->table->columns()->max('order');
        $newOrder = Helpers::calculateNextOrder($maxOrder);

        $baseColumn = [
            'name'  => 'new_column',
            'type'  => 'varchar',
            'order' => $newOrder,
        ];

        $this->table->columns()->create($baseColumn);
        $this->refreshTableWithCounts();
        $this->dispatch('table::column-added');
    }

    #[On('table::relationship-added')]
    public function refreshTableWithCounts(): void
    {
        $this->table = $this->table->fresh([
            'diagram',
            'columns' => function ($query) {
                $query->orderBy('order');
            }
        ])->loadCount([
            'columns',
            'columns as primary_count' => function ($q) {
                $q->where('is_primary', true);
            },
            'columns as foreign_count' => function ($q) {
                $q->where('is_foreign', true);
            },
        ]);
    }

    public function remove(): void
    {
        if (!$this->authorizeDiagramWithAlert('update', $this->table->diagram)) {
            return;
        }

        DB::transaction(function () {
            $this->table->relationships()->delete();
            $this->table->columns()->delete();
            $this->table->delete();
        });

        $this->dispatch('alert', message: "Table Removed");
        $this->dispatch('table::removed');
        $this->dispatch('relationships::fresh', relationships: Diagram::find($this->diagramId)->relationships()->get());
    }

    #[On('table::move.{table.id}')]
    public function updatePosition(int $x, int $y): void
    {
        if(Gate::denies('update', $this->table->diagram)) {
            return;
        }

       $this->table->update([
            'x' => $x,
            'y' => $y,
        ]);

        $this->refreshTableWithCounts();
    }

    public function render(): View
    {
        return view('livewire.diagram.table');
    }
}
