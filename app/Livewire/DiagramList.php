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
class DiagramList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function placeholder()
    {
        return <<<'HTML'
            <div class="h-dvh m-0 p-0 flex justify-center items-center">
                <span class="loading loading-ring loading-xl"></span>
            </div>
        HTML;
    }

    #[Computed]
    public function getDiagrams()
    {
        return Diagram::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->where('team_id', Team::currentTeam()->getKey())
            ->paginate();
    }

    public function render(): View
    {
        return view('livewire.diagram-list');
    }
}
