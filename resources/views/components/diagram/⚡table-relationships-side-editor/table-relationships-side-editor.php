<?php

use App\Concerns\HasSidePanel;
use App\Models\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
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

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Relationship>
     */
    #[Computed]
    public function getRelationships(): Collection
    {
        return $this->table->relationships()->get();
    }
};
