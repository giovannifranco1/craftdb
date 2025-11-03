<?php

namespace App\Livewire;

use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
class Dashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Computed]
    public function getDiagrams()
    {
        sleep(1);

        return Diagram::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->where('team_id', Team::currentTeam()->getKey())
            ->paginate();
    }

    public function placeholder(): View
    {
        return view('components.placeholders.placeholder-dashboard');
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
