<flux:navlist sticky class="w-64 shrink-0 me-10 gap-1" style="padding:0;">
    <flux:navlist.item href="{{ route('dashboard') }}" wire:navigate badge="{{ session()->get('diagrams_count') }}" :current="request()->routeIs('dashboard') && !request()->has('favorites')">{{ __('My Diagrams') }}</flux:navlist.item>
    <flux:navlist.item href="{{ route('dashboard', ['favorites' => true]) }}" wire:navigate :current="request()->routeIs('dashboard') && request()->has('favorites')">{{ __('My Favorites') }}</flux:navlist.item>
</flux:navlist>
