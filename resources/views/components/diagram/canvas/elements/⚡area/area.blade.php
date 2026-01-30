<div
    x-data="runArea(@js($area->x), @js($area->y), @js($area->width), @js($area->height), @js($area->id))"
    x-show="@js($area->id)"
    id="area-{{ $area->id }}"
    area-id="{{ $area->id }}"
    area-box
    @php
        $areaColor = $area->color ?? '#94a3b8';

        if ($area->is_solid ?? false) {
            $colorful = config('color.colorful', []);
            $colorfulSolid = config('color.colorful-solid', []);

            $colorKey = null;

            foreach ($colorful as $key => $hex) {
                if (strtolower($hex) === strtolower($areaColor)) {
                    $colorKey = $key;
                    break;
                }
            }

            if ($colorKey && isset($colorfulSolid[$colorKey])) {
                $areaColor = $colorfulSolid[$colorKey];
            }
        }

        $opacity = $area->is_solid ?? false ? 1 : 0.3;
        $bgColor = \App\Support\Helpers::hexToRgba($areaColor, $opacity);
    @endphp
    :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${width}px; height: ${height}px; max-width: ${width}px; max-height: ${height}px; ${zIndex ? `z-index: ${zIndex};` : ''} border-color: {{ $areaColor }}; background-color: {{ $bgColor }};`"
    class="absolute select-none rounded-xl border-2 border-dashed">

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
                    loadingColor: false,
                    isEventRename: false,
                    name: $wire.entangle('name'),
                    loading: false
                }"
                @area::renaming.window="if (($event.detail?.el || null) === ($el.closest('[area-box]') || $el)) isEventRename = true"
                @area::renamed.window="isEventRename = false; loading = false"
            >

                <div
                    class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-zinc-900 border-2 rounded-md px-2 py-0.5 shadow-md cursor-pointer hover:shadow-lg transition-shadow min-w-[80px]"
                    :style="`border-color: {{ $areaColor }};`"
                >
                    <button
                        x-data
                        type="button"
                        class="inline-flex items-center h-3 w-1.5 justify-center"
                        x-on:mousedown.stop="
                            const areaBox = $el.closest('[area-box]');
                            $dispatch('dropdown-menu::toggle', { areaBox: areaBox || $el });
                            $dispatch('dropdown-menu::opened', { el: areaBox || $el });
                        "
                        x-on:keydown.escape="
                            const areaBox = $el.closest('[area-box]');
                            $dispatch('dropdown-menu::toggle', { areaBox: areaBox || $el });
                        "
                        title="Change area color"
                    >
                        <flux:icon.ellipsis-vertical class="size-3"/>
                    </button>

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
                        <flux:tooltip class="text-[10px] tracking-wide" :content="__('Double click to rename, click circle to change color')">
                            <span
                                x-show="!isEventRename"
                                @dblclick.stop="isEventRename = true"
                                class="text-[10px] font-[Space Mono] font-semibold text-gray-700 dark:text-zinc-300 uppercase tracking-wide truncate"
                            >{{ $area->name ?? 'Area name...' }}</span>
                        </flux:tooltip>
                    </div>

                </div>

                <!-- Dropdown Menu -->
                <x-diagram.canvas.ui.area.dropdown-menu :area="$area" />
            </div>
        </div>

        @include('components.diagram.canvas._partials.area.resize-handle')
    </div>
</div>
