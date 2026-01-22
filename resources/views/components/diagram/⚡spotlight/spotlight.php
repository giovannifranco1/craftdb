<?php

use App\Models\Diagram;
use App\Models\Table;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Livewire\BaseComponent;

new class extends BaseComponent {
    #[Locked]
    public Diagram $diagram;

    public ?string $search = '';

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
    }

    /**
     * @return Collection<int,Table>
     */
    #[Computed]
    public function results(): Collection
    {
        if (empty($this->search)) {
            $empty = new Collection();
            return $empty;
        }

        return $this->diagram->tables()
            ->where('name', 'like', "%{$this->search}%")
            ->withCount('columns')
            ->limit(10)
            ->get();
    }

    public function selectTable(int $tableId): void
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
