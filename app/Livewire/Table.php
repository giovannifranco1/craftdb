<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Table extends Component
{

    public int $x = 0;
    public int $y = 0;

    public function mount(int $x, int $y): void
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function render(): View
    {
        return view('livewire.table');
    }
}
