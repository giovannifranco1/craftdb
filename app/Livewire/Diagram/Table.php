<?php

namespace App\Livewire\Diagram;

use App\Models\Table as TableModel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Table extends Component
{
    protected $listeners = [
        'column-saved' => '$refresh'
    ];

    #[Locked]
    public TableModel $table;

    public function mount(TableModel $table): void
    {
        $this->table = $table;
    }

    #[Computed]
    public function getColumns(): Collection
    {
        return $this->table->columns()->orderByDesc('order')->get();
    }

    public function setColor(string $color): void
    {
        $this->table->color = $color;

        if(!in_array($color, array_merge(config('color.colorful'), config('color.neutral')))) {
            return;
        };

        $this->table->save();

        $this->dispatch('alert', message: "Color Updated");
        $this->dispatch('table::color-saved');
    }

    public function addColum(): void
    {
        $minOrder = $this->table->columns()->min('order');
        $newOrder = $minOrder === null
            ? 100
            : max(1, (int)($minOrder / 2));

        $baseColumn = [
            'name'  => 'new_column',
            'type'  => 'varchar',
            'order' => $newOrder
        ];

        $this->table->columns()->create($baseColumn);
        $this->dispatch('table::column-added');
        $this->dispatch('$refresh')->self();
    }

    public function remove(): void
    {
        DB::transaction(function () {
            $this->table->columns()->delete();
            $this->table->delete();
        });

        $this->dispatch('$refresh')->self();
        $this->dispatch('alert', message: "Table Removed");
        $this->dispatch('table::removed');
    }

    public function render(): View
    {
        return view('livewire.diagram.table');
    }
}
