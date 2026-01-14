<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Livewire\Component;

class MyFavorites extends Component
{
    public function render(): View
    {
        return ViewFacade::make('livewire.my-favorites');
    }
}
