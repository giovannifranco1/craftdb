<div class="w-64 shrink-0 sticky top-20 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-background-dark p-4 min-h-[10rem]">
    <flux:navlist variant="outline" class="gap-1 w-full">
        <flux:navlist.item icon="layout-grid" href="{{ route('dashboard') }}" wire:navigate badge="{{ session()->get('diagrams_count') }}" :current="request()->routeIs('dashboard') && !request()->has('favorites')">{{ __('My Diagrams') }}</flux:navlist.item>
        <flux:navlist.item icon="heart" href="{{ route('dashboard', ['favorites' => true]) }}" wire:navigate :current="request()->routeIs('dashboard') && request()->has('favorites')">{{ __('My Favorites') }}</flux:navlist.item>
        @auth
            @if(auth()->id() === 1)
                <flux:navlist.item icon="chat-bubble-left-ellipsis" href="{{ route('feedback.index') }}" wire:navigate :current="request()->routeIs('feedback.*')">{{ __('Contact Us - Feedback') }}</flux:navlist.item>
            @endif
        @endauth
    </flux:navlist>
</div>
