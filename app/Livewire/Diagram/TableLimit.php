<?php

namespace App\Livewire\Diagram;

use App\Models\Diagram;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class TableLimit extends Component
{
    protected $listeners = [
        'table::added' => '$refresh',
        'table::removed' => '$refresh',
    ];

    #[Locked]
    public Diagram $diagram;
    public int $limit = 30;

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
    }

    #[Computed(cache: true)]
    public function getTableCount(): int
    {
        return $this->diagram->tables()->count();
    }

    public function render(): View
    {
        return view('livewire.diagram.table-limit');
    }
}
