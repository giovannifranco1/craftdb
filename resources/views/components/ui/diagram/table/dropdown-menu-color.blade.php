<div
    x-cloak
    x-show="open"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-1"
    @mousedown.outside.window="open = false"
    @keydown.escape.stop="open = false"
    @mousedown.stop
    @dropdown-menu-opened.window="if (($event.detail?.el || null) !== ($el.closest('[table-box]') || $el)) open = false"
    class="absolute -top-15 p-3 -left-15 z-50 mt-2 min-w-30 w-50 justify-between flex bg-accent items-center text-white rounded-md"
>
    @foreach($table->diagram->getColors() as $color)
        @php($isSelected = $table->color === $color)
        <x-ui.color-circle @mousedown.stop="$wire.setColor('{{ $color }}')" :color="$color" :selected="$isSelected"/>
    @endforeach
</div>
