<?php

namespace App\Livewire\Diagram;

use App\Concerns\HasSidePanel;
use App\Models\Column;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class TableSideEditor extends Component
{
    use HasSidePanel;

    public Table $table;

    public ?int $selectedColumnId = null;

    public bool $showEditForm = false;

    // Inline editing
    public ?int $editingColumnId = null;
    public string $editingColumnName = '';

    // Form properties
    #[Validate('required|string|max:255')]
    public string $columnName = '';

    #[Validate('required|string|max:50')]
    public string $columnType = 'VARCHAR';

    #[Validate('nullable|integer|min:0')]
    public ?int $columnLength = null;

    #[Validate('boolean')]
    public bool $columnIsPrimary = false;

    #[Validate('boolean')]
    public bool $columnIsForeign = false;

    #[Validate('boolean')]
    public bool $columnIsNullable = false;

    #[Validate('nullable|string|max:255')]
    public ?string $columnDefaultValue = null;

    #[Validate('nullable|string')]
    public ?string $columnComment = null;

    public function mount(Table $table): void
    {
        $this->table = $table;
    }

    #[On('table-side-editor::open')]
    public function open(Table $table): void
    {
        $this->table = $table;
        $this->open = true;
        $this->resetForms();

        $this->dispatch('table-side-editor::opened', tableId: $table->id);
    }

    public function openCreateColumnModal(): void
    {
        $this->resetForms();
        $this->js('$flux.modal("create-column").show()');
    }

    #[Computed]
    public function getColumns(): Collection
    {
        return $this->table
            ->columns()
            ->orderByDesc('order')
            ->get();
    }

    public function getForeignKeys(): Collection
    {
        return $this->table
            ->columns()
            ->filterByForeignKeys()
            ->orderByDesc('order')
            ->get();
    }

    #[Computed]
    public function getSelectedColumn()
    {
        if ($this->selectedColumnId === null) {
            return null;
        }

        return $this->table
            ->columns()
            ->whereKey($this->selectedColumnId)
            ->first();
    }

    public function selectColumn(int $columnId): void
    {
        $this->selectedColumnId = $columnId;
        $this->showEditForm = false;
    }

    public function clearSelection(): void
    {
        $this->selectedColumnId = null;
        $this->showEditForm = false;
    }


    public function openEditColumnModal(int $columnId): void
    {
        $column = $this->table->columns()->whereKey($columnId)->first();

        if (!$column) {
            return;
        }

        $this->selectedColumnId = $columnId;
        $this->columnName = $column->name;
        $this->columnType = $column->type;
        $this->columnLength = $column->length;
        $this->columnIsPrimary = $column->is_primary;
        $this->columnIsForeign = $column->is_foreign;
        $this->columnIsNullable = $column->is_nullable;
        $this->columnDefaultValue = $column->default_value;
        $this->columnComment = $column->comment;

        $this->modal("edit-column")->show();
    }

    public function startEditingColumnName(int $columnId): void
    {
        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $this->editingColumnId = $columnId;
        $this->editingColumnName = $column->name;
    }

    public function cancelEditingColumnName(): void
    {
        $this->editingColumnId = null;
        $this->editingColumnName = '';
    }

    public function updateColumnName(int $columnId): void
    {
        $this->validate([
            'editingColumnName' => 'required|string|max:255',
        ]);

        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'name' => $this->editingColumnName,
        ]);

        $this->cancelEditingColumnName();
        $this->dispatch('table::column-saved');
    }

    public function cancelForm(): void
    {
        $this->resetForms();
        $this->js('$flux.modal("create-column").close()');
    }

    public function cancelEditForm(): void
    {
        $this->resetForms();
        $this->js('$flux.modal("edit-column").close()');
    }

    public function createColumn(): void
    {
        $this->validate();

        $maxOrder = $this->table->columns()->max('order') ?? 0;

        Column::create([
            'table_id' => $this->table->id,
            'name' => $this->columnName,
            'type' => $this->columnType,
            'length' => $this->columnLength,
            'is_primary' => $this->columnIsPrimary,
            'is_foreign' => $this->columnIsForeign,
            'is_nullable' => $this->columnIsNullable,
            'default_value' => $this->columnDefaultValue,
            'comment' => $this->columnComment,
            'order' => $maxOrder + 1,
        ]);

        $this->resetForms();
        $this->js('$flux.modal("create-column").close()');
        $this->dispatch('table::column-saved');
    }

    public function updateColumn(): void
    {
        $this->validate();

        $column = $this->getSelectedColumn();
        if (!$column) {
            return;
        }

        $column->update([
            'name' => $this->columnName,
            'type' => $this->columnType,
            'length' => $this->columnLength,
            'is_primary' => $this->columnIsPrimary,
            'is_foreign' => $this->columnIsForeign,
            'is_nullable' => $this->columnIsNullable,
            'default_value' => $this->columnDefaultValue,
            'comment' => $this->columnComment,
        ]);

        $this->resetForms();
        $this->js('$flux.modal("edit-column").close()');
        $this->dispatch('column-updated');
    }

    public function deleteColumn(): void
    {
        $column = $this->getSelectedColumn();
        if (!$column) {
            return;
        }

        $column->delete();
        $this->resetForms();
        $this->dispatch('column-deleted');
    }

    private function resetForms(): void
    {
        $this->showEditForm = false;
        $this->editingColumnId = null;
        $this->editingColumnName = '';
        $this->columnName = '';
        $this->columnType = 'VARCHAR';
        $this->columnLength = null;
        $this->columnIsPrimary = false;
        $this->columnIsForeign = false;
        $this->columnIsNullable = false;
        $this->columnDefaultValue = null;
        $this->columnComment = null;
        $this->resetValidation();
    }

    public function getColumnTypes(): array
    {
        return [
            'VARCHAR' => 'VARCHAR',
            'CHAR' => 'CHAR',
            'TEXT' => 'TEXT',
            'INT' => 'INT',
            'BIGINT' => 'BIGINT',
            'SMALLINT' => 'SMALLINT',
            'TINYINT' => 'TINYINT',
            'DECIMAL' => 'DECIMAL',
            'FLOAT' => 'FLOAT',
            'DOUBLE' => 'DOUBLE',
            'BOOLEAN' => 'BOOLEAN',
            'DATE' => 'DATE',
            'DATETIME' => 'DATETIME',
            'TIMESTAMP' => 'TIMESTAMP',
            'TIME' => 'TIME',
            'YEAR' => 'YEAR',
            'JSON' => 'JSON',
        ];
    }

    public function render()
    {
        return view('livewire.diagram.table-side-editor');
    }
}
