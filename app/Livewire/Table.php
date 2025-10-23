<?php

namespace App\Livewire;

use App\Models\Table as TableModel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

// #[Lazy]
class Table extends Component
{
    public TableModel $table;

    public function mount(TableModel $table): void
    {
        $this->table = $table;
    }

    #[Computed()]
    public function getColumns(): Collection
    {
        return $this->table->columns()
            ->orderByDesc('order')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.table');
    }
}
