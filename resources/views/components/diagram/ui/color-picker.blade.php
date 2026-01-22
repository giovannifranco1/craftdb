@props([
    'item' => null, // Table or Area model
    'type' => 'table', // 'table' or 'area'
    'position' => null, // Custom position classes, defaults based on type
])

@php
    $colorUpdatedEvent = match($type) {
        'table' => 'table::color-updated',
        'area' => 'area::color-updated',
        'note' => 'note::color-updated',
        default => 'table::color-updated',
    };
    $currentColor = $item->color ?? null;
    $colors = $this->diagram->colors;

    // Default positioning based on type
    if ($position === null) {
        $position = match($type) {
            'table' => 'absolute -top-15 -left-15 mt-2 min-w-30 w-43',
            'area', 'note' => 'absolute left-0 top-full mt-2',
            default => 'absolute left-0 top-full mt-2',
        };
    }
@endphp
<div
    x-cloak
    x-show="open"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-1"
    @area::color-updated.window="loadingColor = false"
    @table::color-updated.window="loadingColor = false"
    @area::color-updated-error.window="loadingColor = false"
    @table::color-updated-error.window="loadingColor = false"
    @mousedown.outside.window="open = false"
    @keydown.escape="open = false"
    @mousedown.stop
    @if($type === 'table')
        @dropdown-menu::opened.window="if (($event.detail?.el || null) !== ($el.closest('[table-box]') || $el)) open = false"
    @endif
    class="{{ $position }} p-3 z-50 flex gap-2 bg-accent dark:bg-primary-200 border border-gray-200 dark:border-zinc-700 shadow-lg dark:shadow-zinc-900/50 items-center rounded-md"
    :style="`cursor: ${loadingColor ? 'wait' : ''};`"
>
    @foreach($colors as $color)
        @php($isSelected = $currentColor === $color)
        <x-diagram.ui.color-circle
            @mousedown.stop="loadingColor = true; $wire.setColor('{{ $color }}')"
            :color="$color"
            :selected="$isSelected"
        />
    @endforeach
</div>

