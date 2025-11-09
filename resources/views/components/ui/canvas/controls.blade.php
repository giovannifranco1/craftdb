<div id="controls" class="w-[20rem] h-14 bg-white items-center rounded-lg flex justify-between">
    <button
        class="p-1 ms-2 rounded-sm transition-colors cursor-pointer hover:bg-gray-200"
        type="button"
        @click="$dispatch('add-table')">
            <flux:icon.check class="size-6"/>
    </button>

    <button class="p-1 ms-2 rounded-sm transition-colors cursor-pointer hover:bg-gray-200" type="button" id="zoom-in" @click="Alpine.raw(canvas).zoom(1.155)">
        <flux:icon.magnifying-glass-plus class="size-6"/>
    </button>

    <span @canvas-zoom.window="zoomPercent = Math.round($event.detail.scale * 100);" x-text="`${zoomPercent}%`"></span>

    <button class="p-1 me-2 rounded-sm transition-colors cursor-pointer hover:bg-gray-200" type="button" id="zoom-in" @click="Alpine.raw(canvas).zoom(0.85)">
        <flux:icon.magnifying-glass-minus class="size-6"/>
    </button>
</div>
