<?php

use App\Models\Diagram;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    /** @var array<string, string> */
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

    #[Computed()]
    public function getTableCount(): int
    {
        return $this->diagram->tables()->count();
    }
};
