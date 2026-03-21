<?php

use App\Actions\Diagram\AddColumnToTable as AddColumnToTableAction;
use App\Actions\Diagram\DeleteColumnToTable;
use App\Actions\Diagram\DeleteTable as DeleteTableAction;
use App\Livewire\Data\TableData;
use App\Models\Column;
use App\Models\Table as TableModel;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Livewire\CanvasComponent;
use App\Livewire\Data\ColumnData;
use App\Support\DiagramLimits;
use Livewire\Attributes\Async;

new class extends CanvasComponent {

    /** @var array<string, string>  */
    protected $listeners = [
        'table::column-saved' => 'updateTableData',
        'table-side-editor::column-added'   => 'updateTableData',
        'table-side-editor::column-saved'   => 'updateTableData',
        'table-side-editor::column-deleted' => 'updateTableData',
        'table-side-editor::column-updated' => 'updateTableData',
    ];

    #[Locked]
    public int $tableId;

    /** @var array<int, mixed> */
    public array $columns = [];
    public bool $loadTable;
    public bool $hasColumnsInDatabase;

    #[Validate('required|string|max:255|min:1')]
    public ?string $name = null;

    #[Locked]
    public TableData $tableData;

    public function mount(TableModel $table): void
    {
        $this->initialize($table);
    }

    protected function initialize(TableModel $table): void
    {
        $this->tableId = $table->id;
        $this->diagramId = $table->diagram_id;
        $this->name = $table->name;
        $this->hasColumnsInDatabase = $table->columns_exists ?? false;
        $this->tableData = TableData::fromModel($table);
    }

    /** @return Collection<int, Column> */
    #[Computed]
    public function columns(): Collection
    {
        return $this->table->columns;
    }

    #[Async]
    public function loadColumns(): void
    {
        $this->loadTable = true;
        $this->hasColumnsInDatabase = true;
    }

    #[Async]
    public function unloadColumns(): void
    {
        $this->loadTable = false;
        $this->hasColumnsInDatabase = true;
    }

    #[Computed]
    public function table(): TableModel
    {
        return TableModel::findOrFail($this->tableId);
    }

    public function renameTable(): void
    {
        $this->authorizeDiagram('update');

        $this->validateOnly('name');

        $this->table->update(['name' => $this->name]);
        $this->tableData->name = $this->name ?? '';

        $this->dispatch('table::renamed');
    }

    public function resetName(): void
    {
        $this->authorizeDiagram('update');

        $this->name = $this->table->name;
    }

    public function setColor(string $color): void
    {
        $this->authorizeDiagram('update', dispatches: ['table::color-updated-error']);

        if (!Helpers::isColorValid($color)) return;

        $this->table->color = $color;
        $this->table->save();

        $this->updateTableData();

        $this->dispatch('table::color-updated');
    }

    public function renameColumn(Column $column, ?string $newName): void
    {
        $this->authorizeDiagram('update');

        $validate = Validator::make(['newName' => $newName], ['newName' => 'required|string|max:255|min:1']);

        if ($validate->fails()) {
            $this->dispatch('alert', message: __('Invalid name'), type: 'error');
            $this->dispatch('table::column-renamed');
            return;
        }

        $column->name = $newName ?? '';
        $column->save();

        $this->updateTableData();

        $this->dispatch('table::column-renamed');
    }

    #[Computed]
    public function tableColumnsLimit(): int
    {
        $owner = $this->diagram->team?->owner;
        abort_if($owner === null, 403, 'Team owner not found');
        $limits = DiagramLimits::forUser($owner);

        return $limits['table_columns_limit'];
    }

    public function addColumn(AddColumnToTableAction $action): void
    {
        $this->authorizeDiagram('update');

        if ($this->table->columns()->count() >= $this->tableColumnsLimit) {
            $this->dispatch('alert', message: __('You have reached the maximum number of columns for this table.'), type: 'error');
            return;
        }

        $column = $action->exec($this->table);
        $newColumnId = $column->id;

        $this->updateTableData();

        $this->tableData->columns = array_map(function (ColumnData $columnData) use ($newColumnId) {

            if (($columnData->id ?? null) === $newColumnId) {
                $columnData->__new = true;
            } else {
                $columnData->__new = false;
            }

            return $columnData;
        }, $this->tableData->columns);

        $this->dispatch("table::column-added.{$this->tableId}", columnId: $column->id);
    }

    public function deleteColumn(Column $column, DeleteColumnToTable $action): void
    {
        $this->authorizeDiagram('update');

        $action->exec($column);

        $newResizeColumnPosition = $this->table->resize_column_position + 1;

        $this->table->update(['resize_column_position' => $newResizeColumnPosition]);

        $this->updateTableData();
        $this->dispatch('table-side-editor::column-deleted');
        $this->dispatch('alert', message: __('Column deleted successfully!'));
    }

    #[On('table::relationship-added.{tableId}')]
    #[On('table::column-added.{tableId}')]
    #[On('table-side-editor::column-reordered.{tableId}')]
    public function updateTableData(): void
    {
        $table = $this->table->fresh([
            'columns' => fn ($query) =>
             $query->orderBy('order')->withExists(['relationshipsFrom', 'relationshipsTo']),
        ]);

        if ($table === null) {
            throw new \LogicException('Table não existe mais');
        }

        $table = $table->loadCount([
            'columns',
            'columns as primary_count' => fn ($q) => $q->where('is_primary', true),
            'columns as foreign_count' => fn ($q) => $q->where('is_foreign', true),
        ]);

        $table = $table->loadExists('columns');

        $this->tableData = TableData::fromModel($table);
    }

    public function remove(DeleteTableAction $action): void
    {
        $this->authorizeDiagram('update');

        $action->exec($this->table);

        $this->dispatch('alert', message: __('Table Removed'));
        $this->dispatch('table::removed', tableId: $this->tableId);

        $relationships = $this->diagram->relationships()->get();

        $this->dispatch('relationships::fresh', relationships: $relationships);
    }

    #[On('table::move.{tableId}')]
    public function updatePosition(int $x, int $y): void
    {
        $this->authorizeDiagram('update');

       $this->table->update([
            'x' => $x,
            'y' => $y,
       ]);
    }

    #[On('table::resize.{tableId}')]
    public function updateResizeColumn(int $visibleColumnsCount): void
    {
        $this->authorizeDiagram('update');

        $this->table->update(['resize_column_position' => $visibleColumnsCount]);

        $this->updateTableData();
    }
};
