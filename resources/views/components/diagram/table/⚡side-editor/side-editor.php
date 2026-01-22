<?php

use App\Concerns\HandlesDiagramAuthAlert;
use App\Concerns\HasSidePanel;
use App\Enums\ColumnTypes;
use App\Livewire\Forms\Diagram\ColumnForm;
use App\Models\Column;
use App\Models\Table;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use App\Livewire\BaseComponent;
use App\Models\Diagram;
use App\Models\Relationship;

new class extends BaseComponent {
    use HandlesDiagramAuthAlert;
    use HasSidePanel;

    #[Locked]
    public ?int $tableId = null;

    #[Locked]
    public ?int $selectedColumnId = null;

    public bool $showEditForm = false;
    public ?int $editingColumnId = null;
    public string $editingColumnName = '';
    public bool $isEventUpdateColumn = false;
    public ColumnForm $columnForm;

    public int $diagramId;

    public function mount(Diagram $diagram): void
    {
        $this->diagramId = $diagram->id;
    }

    #[Computed()]
    public function getTable(): ?Table
    {
        return cache()->remember('table_'.$this->tableId, 60, function () {
            return Table::query()->find($this->tableId);
        });
    }

    #[Computed]
    public function diagram(): Diagram
    {
        return Diagram::query()->findOrFail($this->diagramId);
    }

    #[On('table-side-editor::open')]
    public function open(int $tableId): void
    {
        $table = Table::query()->find($tableId);

        if (!$table) {
            $this->open = false;
            $this->dispatch('table-side-editor::closed');
            return;
        }

        $this->tableId = $tableId;
        $this->open = true;

        $this->resetForms();
        $this->normalizeColumnOrders();
        $this->dispatch('table-side-editor::opened', tableId: $tableId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Column>
     */
    #[Computed()]
    public function getColumns(): Collection
    {
        $table = $this->getTable;

        if (!$table) {
            return new Collection();
        }

        return $table->columns()->orderBy('order')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Column>
     */
    #[Computed]
    public function getForeignKeys(): Collection
    {
        $table = $this->getTable();

        if (!$table) {
            return new Collection();
        }

        return $table->foreignKeys ?? new Collection();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Relationship>
     */
    #[Computed]
    public function getRelationships(): Collection
    {
        $table = $this->getTable;
        if (!$table) {
            return new Collection();
        }

        return $table->relationships()->with(['fromTable', 'toTable', 'fromColumn', 'toColumn'])->get();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function getTablesWithColors(): array
    {
        $table = $this->getTable;
        if (!$table) {
            return [];
        }

        $tablesMap = [];
        $tablesMap[$table->id] = $table->color ?? Table::COLOR_DEFAULT;

        $this->getRelationships->each(function (Relationship $rel) use (&$tablesMap): void {

            if ($rel->fromTable && !isset($tablesMap[$rel->from_table_id])) {
                $fromTable = $rel->fromTable;
                $tablesMap[$rel->from_table_id] = $fromTable->color ?? Table::COLOR_DEFAULT;
            }

            if ($rel->toTable && !isset($tablesMap[$rel->to_table_id])) {
                $toTable = $rel->toTable;
                $tablesMap[$rel->to_table_id] = $toTable->color ?? Table::COLOR_DEFAULT;
            }
        });

        return $tablesMap;
    }

    #[Computed]
    public function getSelectedColumn(): ?Column
    {
        if ($this->selectedColumnId === null) {
            return null;
        }

        $table = $this->getTable;
        if (!$table) {
            return null;
        }

        return $table
            ->columns()
            ->whereKey($this->selectedColumnId)
            ->first();
    }

    public function addColumn(): void
    {
        $this->authorizeDiagram('update');

        $table = $this->getTable;

        if (!$table) {
            return;
        }

        $maxOrder = $table->columns()->max('order');
        $newOrder = Helpers::calculateNextOrder($maxOrder);

        $baseColumn = [
            'name'  => 'new_column',
            'type'  => 'varchar',
            'order' => $newOrder,
        ];

        $table->columns()->create($baseColumn);
        $this->dispatch('table-side-editor::column-added');
    }

    public function updateColumnName(int $columnId): void
    {
        $this->authorizeDiagram('update');

        $table = $this->getTable;

        $this->validate([
            'editingColumnName' => 'required|string|max:255',
        ]);

        $table = $this->getTable;
        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'name' => $this->editingColumnName,
        ]);

        $this->cancelEditingColumnName();
        $this->dispatch('table-side-editor::column-saved');
    }

    public function updateColumn(int $columnId): void
    {
        $table = $this->getTable;
        if (!$table || !$this->authorizeDiagramWithAlert('update', $table->diagram)) {
            return;
        }

        $this->isEventUpdateColumn = true;

        $this->columnForm->validate();

        $table = $this->getTable;

        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();

        if (!$column) {
            return;
        }

        $column->update([
            'name'          => $this->columnForm->name,
            'type'          => strtolower($this->columnForm->type),
            'length'        => $this->columnForm->length,
            'is_primary'    => $this->columnForm->isPrimary,
            'is_foreign'    => $this->columnForm->isForeign,
            'is_notnull'    => !$this->columnForm->isNullable,
            'default_value' => $this->columnForm->defaultValue,
            'comment'       => $this->columnForm->comment,
        ]);

        $this->selectedColumnId = null;
        $this->resetForms();

        $this->dispatch('table-side-editor::column-updated');
        $this->dispatch('alert', message: __('Column updated successfully!'));
    }

    public function deleteColumn(): void
    {
        $this->authorizeDiagram('update');

        $column = $this->getSelectedColumn();

        if (!$column) {
            return;
        }

        $column->delete();
        $this->resetForms();

        $this->dispatch('table-side-editor::column-deleted');
        $this->dispatch('alert', message: __('Column deleted successfully!'));
    }

    public function deleteColumnById(int $columnId): void
    {
        $this->authorizeDiagram('update');

        $table = $this->getTable;
        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->relationshipsFrom()->delete();
        $column->relationshipsTo()->delete();
        $column->delete();

        $this->resetForms();
        $this->dispatch('table-side-editor::column-deleted');
        $this->dispatch('alert', message: __('Column deleted successfully!'));
    }

    public function toggleNullable(int $columnId): void
    {
        $table = $this->getTable;
        if (!$table || !$this->authorizeDiagramWithAlert('update', $table->diagram)) {
            return;
        }

        $table = $this->getTable;
        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'is_notnull' => !$column->is_notnull,
        ]);

        $this->dispatch('table::column-saved');
    }

    public function togglePrimaryKey(int $columnId): void
    {
        $table = $this->getTable;
        if (!$table || !$this->authorizeDiagramWithAlert('update', $table->diagram)) {
            return;
        }

        $table = $this->getTable;

        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'is_primary' => !$column->is_primary,
        ]);

        $this->dispatch('table-side-editor::column-saved');
    }

    public function toggleForeignKey(int $columnId): void
    {
        $table = $this->getTable;
        if (!$table || !$this->authorizeDiagramWithAlert('update', $table->diagram)) {
            return;
        }

        $table = $this->getTable();
        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $column->update([
            'is_foreign' => !$column->is_foreign,
        ]);

        $this->dispatch('table-side-editor::column-saved');
    }

    /**
     * Normaliza as ordens das colunas para serem sequenciais (1, 2, 3, 4...)
     */
    private function normalizeColumnOrders(): void
    {
        $table = $this->getTable;

        if (!$table) {
            return;
        }

        $columns = $table->columns()->orderBy('order', 'asc')->get();

        $order = 1;
        foreach ($columns as $column) {
            if ($column->order !== $order) {
                $column->update(['order' => $order]);
            }
            $order++;
        }
    }

    public function moveColumnUp(int $columnId): void
    {
        $table = $this->getTable;
        if (!$table || !$this->authorizeDiagramWithAlert('update', $table->diagram)) {
            return;
        }

        $table = $this->getTable;
        if (!$table) {
            return;
        }

        $this->normalizeColumnOrders();

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        // Encontra a coluna anterior (com ordem menor)
        $previousColumn = $table->columns()
            ->where('order', '<', $column->order)
            ->orderBy('order', 'desc')
            ->first();

        if (!$previousColumn) {
            return; // Já é a primeira coluna
        }

        // Troca as ordens
        $tempOrder = $column->order;
        $column->update(['order' => $previousColumn->order]);
        $previousColumn->update(['order' => $tempOrder]);

        $this->dispatch('table-side-editor::column-reordered');
    }

    public function moveColumnDown(int $columnId): void
    {
        $table = $this->getTable;
        if (!$table || !$this->authorizeDiagramWithAlert('update', $table->diagram)) {
            return;
        }

        $table = $this->getTable;
        if (!$table) {
            return;
        }

        // Primeiro normaliza as ordens
        $this->normalizeColumnOrders();

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        // Encontra a próxima coluna (com ordem maior)
        $nextColumn = $table->columns()
            ->where('order', '>', $column->order)
            ->orderBy('order', 'asc')
            ->first();

        if (!$nextColumn) {
            return; // Já é a última coluna
        }

        // Troca as ordens
        $tempOrder = $column->order;
        $column->update(['order' => $nextColumn->order]);
        $nextColumn->update(['order' => $tempOrder]);

        $this->dispatch('table-side-editor::column-reordered');
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
        $table = $this->getTable;
        if (!$table || !$this->authorizeDiagramWithAlert('update', $table->diagram)) {
            return;
        }

        $table = $this->getTable;
        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();
        if (!$column) {
            return;
        }

        $this->columnForm->name = $column->name;
        $this->columnForm->type = strtoupper($column->type);
        $this->columnForm->length = $column->length;
        $this->columnForm->isPrimary = $column->is_primary;
        $this->columnForm->isForeign = $column->is_foreign;
        $this->columnForm->isNullable = !$column->is_notnull;
        $this->columnForm->defaultValue = $column->default_value;
        $this->columnForm->comment = $column->comment;
    }

    public function startEditingColumnName(int $columnId): void
    {
        /** @var Table|null $table */
        $table = $this->getTable;

        if (!$table) {
            return;
        }

        $column = $table->columns()->whereKey($columnId)->first();
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
        $this->columnForm->reset();
    }

    /**
     * @return array<int, \App\Enums\ColumnTypes>
     */
    public function getColumnTypes(): array
    {
        return ColumnTypes::cases();
    }
};
