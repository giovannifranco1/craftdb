<?php

namespace App\Livewire;

use Livewire\Component;

class Table extends Component
{

    public int $x = 0;
    public int $y = 0;

    public function mount(int $x, int $y){
        $this->x = $x;
        $this->y = $y;
    }

    public function render()
    {
        return view('livewire.table');
    }
}
