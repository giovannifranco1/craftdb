<?php

use App\Models\Diagram;
use App\Models\Table;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public Diagram $diagram;

    public ?string $search = '';

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
    }

    #[Computed]
    public function results()
    {
            if (empty($this->search)) {
            return collect();
        }

        return $this->diagram->tables()
            ->where('name', 'like', "%{$this->search}%")
            ->withCount('columns')
            ->limit(10)
            ->get();
    }

    public function selectTable(int $tableId)
    {
        $table = Table::find($tableId);

        if (!$table) {
            return;
        }

        $this->dispatch('spotlight::table-selected', [
            'tableId' => $table->id,
            'x' => $table->x ?? 0,
            'y' => $table->y ?? 0,
        ]);
    }
};
