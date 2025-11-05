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
        return $this->table->columns()
            ->orderByDesc('order')
            ->get();
    }

    public function render()
    {
        return view('livewire.diagram.table-side-editor');
    }
}
