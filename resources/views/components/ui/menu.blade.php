@php
    $menuTeamId = \App\Models\Team\Team::currentTeam()?->getKey();
    $menuCounts = $menuTeamId
        ? \App\Models\Diagram::query()->where('team_id', $menuTeamId)
            ->selectRaw('count(*) as total, sum(case when is_favorite then 1 else 0 end) as favorites')
            ->first()
        : null;
    $menuDiagramsCount = (int) ($menuCounts?->total ?? 0);
    $menuFavoritesCount = (int) ($menuCounts?->favorites ?? 0);
@endphp
<div class="w-64 shrink-0 sticky top-20 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-background-dark p-4 min-h-[10rem]">
    <flux:navlist variant="outline" class="gap-1 w-full">
        <flux:navlist.item icon="layout-grid" href="{{ route('dashboard') }}" wire:navigate badge="{{ $menuDiagramsCount }}" :current="request()->routeIs('dashboard') && !request()->has('favorites')">{{ __('My Diagrams') }}</flux:navlist.item>
        <flux:navlist.item icon="heart" href="{{ route('dashboard', ['favorites' => true]) }}" wire:navigate badge="{{ $menuFavoritesCount }}" :current="request()->routeIs('dashboard') && request()->has('favorites')">{{ __('My Favorites') }}</flux:navlist.item>
    </flux:navlist>
</div>
