<flux:navlist sticky class="w-64 shrink-0 me-10 gap-1" style="padding:0;">
    <flux:navlist.item href="{{ route('home') }}" wire:navigate badge="{{ 0  }}" :current="request()->routeIs('home') && !request()->has('favorites')">My Diagrams</flux:navlist.item>
    <flux:navlist.item href="{{ route('home', ['favorites' => true]) }}" wire:navigate :current="request()->routeIs('home') && request()->has('favorites')">My Favorites</flux:navlist.item>
</flux:navlist>
