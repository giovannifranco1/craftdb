@props([
    'area' => null
])

<div
    x-data="{
        open: false,
     }"
    x-bind:class="open ? 'opacity-100 pointer-events-auto translate-y-0 duration-150' : 'opacity-0 pointer-events-none -translate-y-1 duration-150'"
    @keydown.escape="open = false;"
    @mousedown.outside="if (!Alpine.raw(canvas).getIsSpacePressed()) open = false;"
    @dropdown-menu::opened.window="if (($event.detail?.el || null) !== ($el.closest('[area-box]') || $el)) open = false"
    @dropdown-menu::toggle.window="if (($event.detail?.areaBox || null) === ($el.closest('[area-box]') || $el)) open = !open"
    class="absolute -left-16 -top-14 z-50 mt-2 min-w-60 w-100 justify-between flex gap-2 items-center text-white rounded-md">
    <flux:button.group>

        <flux:button x-data="{ open: false, loadingColor: false }" @mousedown.stop="open = !open" variant="primary" icon:trailing="chevron-down">
            <x-diagram.canvas.ui.color-circle :color="$area->color"/>
            <x-diagram.canvas.ui.color-picker :item="$area" type="area" />
        </flux:button>

        <flux:button
            @mousedown.stop="$wire.toggleSolid()"
            variant="primary"
            class="text-xs {{ ($area->is_solid ?? false) ? '' : 'opacity-50' }}"
        >
            <flux:icon.paint-brush variant="solid" class="size-3.5"/>
            {{ $area->is_solid ?? false ? __('Transparency') : __('Solid') }}
        </flux:button>

        <flux:button @mousedown.stop="$wire.remove()" variant="primary" square icon="trash" color="red"/>
    </flux:button.group>
</div>
