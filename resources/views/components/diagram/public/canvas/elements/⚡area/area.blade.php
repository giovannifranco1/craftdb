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
    class="absolute select-none rounded-xl border-2 border-dashed">

    <!-- Area Content -->
    <div class="rounded-xl w-full h-full flex flex-col relative">
        <div class="px-3 py-3 relative">
            <div class="absolute -top-2.5 left-3 z-20">

                <div
                    class="inline-flex items-center gap-1.5 bg-white dark:bg-zinc-900 border-2 rounded-md px-2 py-0.5 shadow-md cursor-pointer hover:shadow-lg transition-shadow min-w-[80px]"
                    :style="`border-color: {{ $areaColor }};`"
                    title="Double click to rename, click circle to change color"
                >
                    <div
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

                        <span class="text-[10px] tracking-wide font-semibold text-gray-700 dark:text-zinc-300 uppercase tracking-wide truncate">
                            {{ $area->name ?? 'Area name...' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
