<?php

use App\Concerns\HandlesDiagramAuthAlert;
use App\Actions\Diagram\DeleteDiagram as DeleteDiagramAction;
use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Livewire\BaseComponent;
use Livewire\WithPagination;

new #[Title('Dashboard')] class extends BaseComponent
{
    use WithPagination, HandlesDiagramAuthAlert;

    #[Url]
    public string $search = '';

    #[Url]
    public bool $favorites = false;

    #[Url]
    public int $orderByDesc = 0;

    public ?int $diagramToDelete = null;

    public Team $team;

    public function mount(): void
    {
        $this->team = Team::currentTeam();
        session()->put('diagrams_count',
        Cache::remember(
            "diagrams_count_{$this->favorites}_{$this->search}_{$this->orderByDesc}",
            now()->addMinutes(5),
            fn() => Diagram::query()
                ->where('team_id', Team::currentTeam()->getKey())
                ->count()
        ));
    }

    #[Computed]
    public function diagrams(): LengthAwarePaginator
    {
        session()->put('diagrams_count', Cache::remember(
            "diagrams_count_{$this->favorites}_{$this->search}_{$this->orderByDesc}",
            now()->addMinutes(5),
            fn() => Diagram::query()
                ->where('team_id', Team::currentTeam()->getKey())
                ->count()
        ));

        return Diagram::query()
            ->when($this->favorites, fn ($q) => $q->where('is_favorite', true))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount([
                'tables' => function ($query) {
                    $query->withoutGlobalScope(\App\Models\Scopes\TableAccessible::class);
                },
                'areas',
                'notes'
            ])
            ->where('team_id', Team::currentTeam()->getKey())
            ->orderBy('created_at', $this->orderByDesc ? 'asc' : 'desc')
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

    protected $listeners = ['diagram-updated' => '$refresh'];

    public function editDiagram(Diagram $diagram): void
    {
        $this->authorizeDiagram('update', $diagram);

        $this->dispatch('open-diagram-edit-modal', diagramId: $diagram->id);
    }

    public function confirmDelete(int $diagramId): void
    {
        $this->diagramToDelete = $diagramId;
    }

    public function deleteDiagram(DeleteDiagramAction $action): void
    {
        if (!$this->diagramToDelete) {
            return;
        }

        $diagram = Diagram::findOrFail($this->diagramToDelete);

        $this->authorizeDiagram('delete', $diagram);

        $action->exec($diagram);

        $this->reset('diagramToDelete');
        $this->modal('confirm-delete-diagram')->close();
        $this->dispatch('alert', message: __('Diagram :name was deleted', ['name' => $diagram->name]));
    }

    public function placeholder(): View
    {
        return view('placeholders::placeholder-dashboard');
    }
};
