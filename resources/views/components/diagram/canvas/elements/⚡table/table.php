<?php

use App\Actions\Diagram\AddColumnToTable as AddColumnToTableAction;
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

    /** @var Collection<int,Column> */
    public $columns = [];
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
        $this->hasColumnsInDatabase = $table->columns_exists;
        $this->tableData = TableData::fromModel($table);
    }

    #[Computed]
    public function columns(): Collection
    {
        return $this->table->columns;
    }

    public function loadColumns(): void
    {
        $this->loadTable = true;
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
        $this->tableData->name = $this->name;

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

        $column->name = $newName;
        $column->save();

        $this->updateTableData();

        $this->dispatch('table::column-renamed');
    }

    public function addColumn(AddColumnToTableAction $action): void
    {
        $this->authorizeDiagram('update');

        if ($this->table->columns()->count() >= config('diagram.table_columns_limit')) {
            $this->dispatch('alert', message: __('You have reached the maximum number of columns for this table.'), type: 'error');
            return;
        }

        $column = $action->exec($this->table);

        $this->updateTableData();
        $this->dispatch('table::column-added', columnId: $column->id);
    }

    #[On('table::relationship-added.{tableId}')]
    #[On('table-side-editor::column-reordered.{tableId}')]
    public function updateTableData(): void
    {
        $table = $this->table->fresh([
            'columns' => fn ($query) => $query->orderBy('order'),
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

        $relationships = $this->diagram->relationships()->get() ?? collect();

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
};
