<?php

use App\Models\Diagram;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use App\Livewire\BaseComponent;

new class extends BaseComponent {
    /** @var array<string, string> */
    protected $listeners = [
        'table::added' => '$refresh',
        // 'table::removed' => '$refresh',
    ];

    #[Locked]
    public Diagram $diagram;
    public int $limit;

    public function mount(Diagram $diagram): void
    {
        $this->diagram = $diagram;
        $this->limit = config('diagram.table_limit');
    }

    #[Computed()]
    public function tableCount(): int
    {
        return $this->diagram->tables()->count();
    }
};
