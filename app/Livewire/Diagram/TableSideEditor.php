<?php

namespace App\Livewire\Diagram;

use App\Concerns\HasSidePanel;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

final class TableSideEditor extends Component
{
    use HasSidePanel;

    public Table $table;

    public ?int $selectedColumnId = null;

    public function mount(Table $table): void
    {
        $this->table = $table;
    }

    #[On('table-side-editor.open')]
    public function open(Table $table): void
    {
        $this->table = $table;
        $this->open = true;
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
    }

    public function clearSelection(): void
    {
        $this->selectedColumnId = null;
    }

    public function render()
    {
        return view('livewire.diagram.table-side-editor');
    }
}
