<div
    x-data="runArea(@js($area->x), @js($area->y), @js($area->width), @js($area->height), @js($area->id))"
    x-show="@js($area->id)"
    id="area-{{ $area->id }}"
    area-id="{{ $area->id }}"
    area-box
    @php
        $areaColor = $area->color ?? '#94a3b8';
        $bgColor = \App\Support\Helpers::hexToRgba($areaColor, 0.1);
    @endphp
    :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${width}px; height: ${height}px; max-width: ${width}px; max-height: ${height}px; ${zIndex ? `z-index: ${zIndex};` : ''} border-color: {{ $areaColor }}; background-color: {{ $bgColor }};`"
    class="absolute select-none rounded-xl shadow-lg dark:shadow-zinc-900/50 border-2 border-dashed">

    <!-- Area Content -->
    <div class="rounded-xl w-full h-full flex flex-col relative">
        <!-- Header -->
        <div
            x-data="{
                isEventRename: false,
                name: $wire.entangle('name'),
                loading: false
            }"
            @area::renaming.window="if (($event.detail?.el || null) === ($el.closest('[area-box]') || $el)) isEventRename = true"
            @area::renamed.window="isEventRename = false; loading = false"
            class="px-3 py-3 relative"
        >
            <!-- Label Badge -->
            <div
                class="absolute -top-2.5 left-3 z-20"
                x-data="{
                    open: false,
                    loadingColor: false,
                    isEventRename: false,
                    name: $wire.entangle('name'),
                    loading: false
                }"
                @area::renaming.window="if (($event.detail?.el || null) === ($el.closest('[area-box]') || $el)) isEventRename = true"
                @area::renamed.window="isEventRename = false; loading = false"
            >

                <div
                    class="inline-flex items-center gap-1.5 bg-white dark:bg-zinc-900 border-2 rounded-md px-2 py-0.5 shadow-md cursor-pointer hover:shadow-lg transition-shadow min-w-[80px]"
                    :style="`border-color: {{ $areaColor }};`"
                    title="Double click to rename, click circle to change color"
                >
                    <div
                        @mousedown.stop="open = !open"
                        @keydown.escape.stop="open = false"
                        class="size-2 rounded-full flex-shrink-0"
                        :style="`background-color: {{ $areaColor }};`"
                        title="Change area color"
                    ></div>

                    <div class="flex items-center gap-1 min-w-0 flex-1">
                        <div
                            wire:loading
                            wire:target="renameArea"
                            class="animate-spin inline-block size-2 border border-current border-t-transparent text-gray-600 dark:text-zinc-400 rounded-full flex-shrink-0"
                            role="status"
                            aria-label="loading">
                            <span class="sr-only">Loading...</span>
                        </div>

                        <input
                            name="name"
                            x-show="isEventRename"
                            x-ref="inputArea"
                            x-trap="isEventRename"
                            x-model="name"
                            type="text"
                            class="bg-transparent border-none outline-none shadow-none text-[10px] font-semibold text-gray-700 dark:text-zinc-300 uppercase tracking-wide p-0 m-0 w-full min-w-[80px]"
                            style="background: transparent !important; box-shadow: none !important;"
                            @mousedown.stop
                            @keydown.stop
                            @keydown.enter="loading = true; $wire.renameArea()"
                            @keydown.escape.stop="isEventRename = false; $wire.resetName()"
                            @area::renamed.window="isEventRename = false; loading = false"
                            @blur="isEventRename = false; $wire.resetName()"
                            autocomplete="off"
                            placeholder="Area name..."
                        />
                        <span
                            x-show="!isEventRename"
                            @dblclick.stop="isEventRename = true"
                            class="text-[10px] font-[Space Mono] font-semibold text-gray-700 dark:text-zinc-300 uppercase tracking-wide truncate"
                        >{{ $area->name ?? 'Area name...' }}</span>
                    </div>

                    <button
                        @mousedown.stop="$wire.remove()"
                        wire:loading.attr="disabled"
                        class="flex-shrink-0 size-3.5 flex items-center justify-center rounded hover:bg-red-100 dark:hover:bg-red-900/20 transition-colors group"
                        title="Delete area"
                    >
                        <flux:icon.trash class="size-2.5 text-red-500"/>
                    </button>
                </div>

                <!-- Color Picker Menu -->
                <x-diagram.canvas.ui.color-picker :item="$area" type="area" position="absolute -left-16 -top-12" />
            </div>
        </div>

        <!-- Top -->
        <div
            class="absolute top-0 left-0 right-0 h-2 cursor-ns-resize z-10 resize-handle"
            data-resize="top"
            @mousedown.stop="startResize('top', $event)"
        ></div>
        <!-- Right -->
        <div
            class="absolute top-0 right-0 bottom-0 w-2 cursor-ew-resize z-10 resize-handle"
            data-resize="right"
            @mousedown.stop="startResize('right', $event)"
        ></div>
        <!-- Bottom -->
        <div
            class="absolute bottom-0 left-0 right-0 h-2 cursor-ns-resize z-10 resize-handle"
            data-resize="bottom"
            @mousedown.stop="startResize('bottom', $event)"
        ></div>
        <!-- Left -->
        <div
            class="absolute top-0 left-0 bottom-0 w-2 cursor-ew-resize z-10 resize-handle"
            data-resize="left"
            @mousedown.stop="startResize('left', $event)"
        ></div>
        <!-- Corner Handles -->
        <!-- Top-Left -->
        <div
            class="absolute top-0 left-0 w-3 h-3 cursor-nwse-resize z-10 resize-handle"
            data-resize="top-left"
            @mousedown.stop="startResize('top-left', $event)"
        ></div>
        <!-- Top-Right -->
        <div
            class="absolute top-0 right-0 w-3 h-3 cursor-nesw-resize z-10 resize-handle"
            data-resize="top-right"
            @mousedown.stop="startResize('top-right', $event)"
        ></div>
        <!-- Bottom-Right -->
        <div
            class="absolute bottom-0 right-0 w-3 h-3 cursor-nwse-resize z-10 resize-handle"
            data-resize="bottom-right"
            @mousedown.stop="startResize('bottom-right', $event)"
        ></div>
        <!-- Bottom-Left -->
        <div
            class="absolute bottom-0 left-0 w-3 h-3 cursor-nesw-resize z-10 resize-handle"
            data-resize="bottom-left"
            @mousedown.stop="startResize('bottom-left', $event)"
        ></div>
    </div>
</div>
