<?php

namespace App\Livewire\Diagram;

use App\Concerns\HasSidePanel;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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

    #[Computed(persist: true)]
    public function getColumns(): Collection
    {
        return $this->table->columns->sortBy('order');
    }

    #[Computed(persist: true)]
    public function getForeignKeys(): ?Collection
    {
        return $this->table?->foreignKeys ?? new Collection();
    }

    #[Computed]
    public function getRelationships(): Collection
    {
        return $this->table->relationships()->with(['fromTable', 'toTable', 'fromColumn', 'toColumn'])->get();
    }

    #[Computed]
    public function getTablesWithColors(): array
    {
        $tablesMap = [];
        $tablesMap[$this->table->id] = $this->table->color ?? Table::COLOR_DEFAULT;

        $this->getRelationships()->each(function (Model $rel) use (&$tablesMap): void {

            if ($rel->fromTable && !isset($tablesMap[$rel->from_table_id])) {
                $tablesMap[$rel->from_table_id] = $rel->fromTable->color ?? Table::COLOR_DEFAULT;
            }
            if ($rel->toTable && !isset($tablesMap[$rel->to_table_id])) {
                $tablesMap[$rel->to_table_id] = $rel->toTable->color ?? Table::COLOR_DEFAULT;
            }
        });

        return $tablesMap;
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

    public function addColumn(): void
    {
        $minOrder = $this->table->columns()->min('order');
        $newOrder = $minOrder === null
        ? 100
        : max(1, (int) ($minOrder / 2));

        $baseColumn = [
            'name'  => 'new_column',
            'type'  => 'varchar',
            'order' => $newOrder,
        ];

        $this->table->columns()->create($baseColumn);
        $this->dispatch('table-side-editor::column-added');
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

    public function updateColumn(int $columnId): void
    {
        $this->validate();

        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'name' => $this->columnName,
            'type' => strtolower($this->columnType),
            'length' => $this->columnLength,
            'is_primary' => $this->columnIsPrimary,
            'is_foreign' => $this->columnIsForeign,
            'is_nullable' => $this->columnIsNullable,
            'default_value' => $this->columnDefaultValue,
            'comment' => $this->columnComment,
        ]);

        $this->resetForms();
        $this->dispatch('table::column-saved');
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

    public function deleteColumnById(int $columnId): void
    {
        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->delete();

        // Se a coluna deletada estava selecionada, limpar a seleção
        if ($this->selectedColumnId === $columnId) {
            $this->resetForms();
        }

        $this->dispatch('table::column-saved');
    }

    public function toggleNullable(int $columnId): void
    {
        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'is_nullable' => !$column->is_nullable,
        ]);

        $this->dispatch('table::column-saved');
    }

    public function togglePrimaryKey(int $columnId): void
    {
        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'is_primary' => !$column->is_primary,
        ]);

        $this->dispatch('table::column-saved');
    }

    public function toggleForeignKey(int $columnId): void
    {
        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'is_foreign' => !$column->is_foreign,
        ]);

        $this->dispatch('table::column-saved');
    }

    public function selectColumn(?int $columnId): void
    {
        if ($columnId === null) {
            $this->selectedColumnId = null;
            $this->resetForms();
            return;
        }

        $this->selectedColumnId = $this->selectedColumnId === $columnId ? null : $columnId;

        if ($this->selectedColumnId === $columnId) {
            $this->loadColumnData($columnId);
        } else {
            $this->resetForms();
        }
    }

    public function loadColumnData(int $columnId): void
    {
        $column = $this->table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $this->columnName = $column->name;
        $this->columnType = strtoupper($column->type);
        $this->columnLength = $column->length;
        $this->columnIsPrimary = $column->is_primary;
        $this->columnIsForeign = $column->is_foreign;
        $this->columnIsNullable = $column->is_nullable;
        $this->columnDefaultValue = $column->default_value;
        $this->columnComment = $column->comment;
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
