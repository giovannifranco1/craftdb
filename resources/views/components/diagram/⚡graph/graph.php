<?php

use App\Models\Diagram;
use App\Models\Relationship;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Livewire\BaseComponent;

new class extends BaseComponent {
    public Diagram $diagram;

    /** @var array<string, string> */
    protected $listeners = [
        'table::added' => '$refresh',
        'table::removed' => '$refresh',
        'table::color-updated' => '$refresh',
        'table::relationship-added' => '$refresh',
    ];

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
    }

    /**
     * @return Collection<int,Relationship>
     */
    #[Computed(cache: true)]
    public function getRelationships(): Collection
    {
        return $this->diagram
            ->relationships()
            ->with(['fromTable', 'toTable', 'fromColumn', 'toColumn'])
            ->get();
    }

    /**
     * @return array<int, string>
     */
    #[Computed(cache: true)]
    public function getTablesWithColors(): array
    {
        $tablesMap = [];

        $this->diagram->tables()->each(function ($table) use (&$tablesMap) {
            $tablesMap[$table->id] = $table->color ?? \App\Models\Table::COLOR_DEFAULT;
        });

        return $tablesMap;
    }

    #[On('table::added')]
    #[On('table::removed')]
    #[On('table::color-updated')]
    #[On('table::relationship-added')]
    public function refreshGraph(): void
    {
        // Refresh the diagram to get latest data
        $this->diagram->refresh();

        // Dispatch event to JavaScript to re-render the graph
        $this->dispatch('diagram-graph::refresh');
    }
};
