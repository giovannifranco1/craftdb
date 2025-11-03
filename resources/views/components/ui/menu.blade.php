<flux:navlist sticky class="w-64 shrink-0 me-10 gap-1" style="padding:0;">
    <flux:navlist.item href="{{ route('home') }}" wire:navigate badge="{{ 6 }}" :current="request()->routeIs('home')">My Diagrams</flux:navlist.item>
    <flux:navlist.item href="{{ route('home.my-favorites') }}" wire:navigate :current="request()->routeIs('home.my-favorites')">My Favorites</flux:navlist.item>
</flux:navlist>
