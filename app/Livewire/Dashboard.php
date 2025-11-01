<?php

namespace App\Livewire;

use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Computed]
    public function getDiagrams()
    {
        return Diagram::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->where('team_id', Team::currentTeam()->getKey())
            ->paginate(1);
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
