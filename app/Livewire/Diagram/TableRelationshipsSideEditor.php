<?php

namespace App\Livewire\Diagram;

use App\Concerns\HasSidePanel;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

final class TableRelationshipsSideEditor extends Component
{
    use HasSidePanel;

    public Table $table;

    public function mount(Table $table): void
    {
        $this->table = $table;
    }

    #[On('table-relationships-side-editor.open')]
    public function open(Table $table): void
    {
        $this->open = true;
        $this->table = $table;
        $this->title = 'Relationships - '.$table->name;
    }

    #[Computed]
    public function getRelationships(): Collection
    {
        return $this->table->relationships()->get();
    }

    public function render()
    {
        return view('livewire.diagram.table-relationships-side-editor');
    }
}
