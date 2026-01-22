<?php

use App\Models\Diagram;
use App\Models\Relationship;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Livewire\BaseComponent;

new class extends BaseComponent {
    public Diagram $diagram;

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
    }

    /**
     * @return Collection<int,Relationship>
     */
    #[Computed]
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
    #[Computed]
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
    #[On('relationships::fresh')]
    public function refreshGraph(): void
    {
        $this->diagram->refresh();
        $this->dispatch('diagram-graph::refresh');
    }
};
