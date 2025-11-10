@props([
    'table' => null
])

<div x-data="{ open: false }" class="relative w-5 h-5 rounded text-white text-xs flex items-center justify-center font-bold table-header-icon">
    <button
        type="button"
        class="inline-flex items-center p-2 justify-center"
        @mousedown.stop="open = !open; $dispatch('dropdown-menu-opened', { el: $el.closest('[table-box]') || $el })"
        @keydown.escape="open = false"
    >
        <flux:icon.sheet class="size-3"/>
    </button>
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
        @dropdown-menu-opened.window="if (($event.detail?.el || null) !== ($el.closest('[table-box]') || $el)) open = false"
        class="absolute -top-20 -left-62 z-50 mt-2 min-w-60 w-100 justify-between flex gap-2 items-center text-white rounded-md"
    >
        <flux:button.group>

            <flux:button x-data="{ open: false }" @mousedown.stop="open = !open" variant="primary" icon:trailing="chevron-down">
                <x-ui.color-circle :color="$table->color"/>
                <x-ui.diagram.table.dropdown-menu-color :table="$table" />
            </flux:button>

            <flux:button @mousedown.stop="$wire.addColum()" variant="primary" class="font-[Poppins] text-xs">
                <flux:icon.plus-circle variant="solid" class="size-4"/>
                Add Column
            </flux:button>

            <flux:button @mousedown.stop="$wire.addColum()" variant="primary" class="font-[Space_Mono] text-sm">
                1:1
            </flux:button>

            <flux:button @mousedown.stop="$wire.addColum()" variant="primary" class="font-[Space_Mono] text-sm">
                1:n
            </flux:button>

            <flux:button @mousedown.stop="$wire.addColum()" variant="primary" class="font-[Space_Mono] text-sm">
                n:n
            </flux:button>

            <flux:button variant="primary" class="font-[Poppins] text-xs">
                <flux:icon.plus-circle variant="solid" class="size-4"/>
                Renomear
            </flux:button>

            <flux:button @mousedown.stop="$wire.remove()" variant="primary" square icon="trash" color="red" class="font-[Kalam]"/>
        </flux:button.group>
    </div>
</div>
