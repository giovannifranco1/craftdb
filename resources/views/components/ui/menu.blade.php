<flux:sidebar class="w-64 shrink-0   me-10" style="padding:0;">
    <flux:sidebar.nav>
        <flux:sidebar.item href="{{ route('home') }}" wire:navigate badge="{{ 6 }}" :current="request()->routeIs('home')">My Diagrams</flux:sidebar.item>
        <flux:sidebar.item href="{{ route('home.my-favorites') }}" wire:navigate :current="request()->routeIs('home.my-favorites')">My Favorites</flux:sidebar.item>
    </flux:sidebar.nav>
</flux:sidebar>
