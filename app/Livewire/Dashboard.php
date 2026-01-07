<?php

namespace App\Livewire;

use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
#[Title('Dashboard')]
class Dashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public bool $favorites = false;

    public function mount(): void
    {
        session()->put('diagrams_count', Diagram::query()
            ->where('team_id', Team::currentTeam()->getKey())
            ->count());
    }

    #[Computed]
    public function getDiagrams(): LengthAwarePaginator
    {
        return Diagram::query()
            ->when($this->favorites, fn ($q) => $q->where('is_favorite', true))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->where('team_id', Team::currentTeam()->getKey())
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function resetSearch(): void
    {
        $this->reset('search');
    }

    public function toggleFavorite(Diagram $diagram): void
    {
        $diagram->is_favorite = !$diagram->isFavorite();
        $diagram->timestamps = false;
        $diagram->save();

        $this->dispatch('diagram::favorite-toggled', $diagram->id);
        $this->dispatch('alert', message: $diagram->isFavorite() ? __('Diagram :name was added to your favorites', ['name' => $diagram->name]) : __('Diagram :name was removed from your favorites', ['name' => $diagram->name]));
    }

    public function placeholder(): View
    {
        return view('components.placeholders.placeholder-dashboard');
    }
}
