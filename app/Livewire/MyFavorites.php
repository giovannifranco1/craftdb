<?php

namespace App\Livewire;

use Livewire\Attributes\Lazy;
use Livewire\Component;

class MyFavorites extends Component
{
    public function mount() {
        sleep(1);
    }
    public function render()
    {
        return view('livewire.my-favorites');
    }
}
