<?php

use App\Models\Diagram;
use App\Models\Relationship;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Livewire\BaseComponent;
use App\Models\Scopes\TableAccessible;
use App\Models\Table;

new class extends BaseComponent {
    public Diagram $diagram;
    public bool $loadGraph = false;

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;

        /** @var User $user */
        $user = Auth::user();

        if ($this->diagram->exists && $user->hasDiagramPremiumAccess($this->diagram)) {
            $this->loadGraph = true;
        }
    }

    #[On('diagram-graph::load')]
    public function loadGraph(Diagram $diagram): void
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->hasDiagramPremiumAccess($diagram)) {
            return;
        }

        $this->loadGraph = true;
        $this->modal("diagram-graph")->show();
        $this->diagram = $diagram;
        $this->dispatch('diagram-graph::refresh');
    }

    /**
     * @return Collection<int,Relationship>
     */
    #[Computed]
    public function relationships(): Collection
    {
        return $this->diagram
            ->relationships()
            ->with([
                'fromTable' => fn($q) => $q->withoutGlobalScope(TableAccessible::class),
                'toTable' => fn($q) => $q->withoutGlobalScope(TableAccessible::class),
                'fromColumn',
                'toColumn'
            ])
            ->get();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function tablesWithColors(): array
    {
        $tablesMap = [];

        $this->diagram
            ->tables()
            ->withoutGlobalScope(TableAccessible::class)
            ->each(function ($table) use (&$tablesMap) {
                $tablesMap[$table->id] = $table->color ?? Table::COLOR_DEFAULT;
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
