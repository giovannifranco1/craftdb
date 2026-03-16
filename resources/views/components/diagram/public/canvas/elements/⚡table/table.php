<?php

use App\Livewire\Data\TableData;
use App\Models\Column;
use App\Models\Table as TableModel;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use App\Livewire\CanvasComponent;
use App\Livewire\Contracts\PublicCanvas;
use App\Support\DiagramLimits;

new class extends CanvasComponent implements PublicCanvas {

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

    #[Computed]
    public function tableColumnsLimit(): int
    {
        $owner = $this->table->diagram->team->owner;
        $limits = DiagramLimits::forUser($owner);

        return $limits['table_columns_limit'];
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
};
