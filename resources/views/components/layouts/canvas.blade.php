<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head-canvas')
    </head>

    <body>
        <div x-data="runCanvas()" class="container">
            <canvas id="canvas"></canvas>

            <svg x-data="runSvgLines(@js($relationships))" id="svg-overlay">
                <g id="lines-layer"></g>
            </svg>

            <div class="tables">
                {{ $slot }}
            </div>

            <div id="controls" class="w-[20rem] h-14 bg-white items-center rounded-lg flex justify-between">
                <button class="p-1 ms-2 rounded-sm transition-colors cursor-pointer hover:bg-gray-200" type="button" id="zoom-in" @click="Alpine.raw(canvas).zoom(1.155)">
                    <flux:icon.magnifying-glass-plus class="size-6"/>
                </button>

                <span @canvas-zoom.window="zoomPercent = Math.round($event.detail.scale * 100);" x-text="`${zoomPercent}%`"></span>

                <button class="p-1 me-2 rounded-sm transition-colors cursor-pointer hover:bg-gray-200" type="button" id="zoom-in" @click="Alpine.raw(canvas).zoom(0.85)">
                    <flux:icon.magnifying-glass-minus class="size-6"/>
                </button>
            </div>
        </div>

        @fluxScripts
        <livewire:diagram.table-side-editor/>
        <livewire:diagram.table-relationships-side-editor/>
    </body>
</html>


