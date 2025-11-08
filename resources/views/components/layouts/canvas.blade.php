<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head-canvas')
    </head>

    <body>
        <x-alert/>

        <div x-data="runCanvas()" class="container">

            <canvas id="canvas"></canvas>

            <svg x-data="runSvgLines(@js($relationships))" id="svg-overlay">
                <g id="lines-layer"></g>
            </svg>

            <div class="tables">
                {{ $slot }}
            </div>

            {{-- Loading init page --}}
            <div x-data="{loading: true}" x-show="loading" @tables-inicializeted.window="loading = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-6">
                    <div class="relative w-16 h-16">
                        <div class="absolute inset-0 rounded-full border-2 border-gray-300/30 border-t-white animate-spin"></div>
                        <div class="absolute inset-2 rounded-full border-2 border-gray-400/20 border-r-white animate-spin" style="animation-duration: 1.2s; animation-direction: reverse;"></div>
                    </div>

                    <div class="flex flex-col items-center gap-3">
                        <span class="text-base font-medium text-gray-200">Carregando tabelas</span>
                        <div class="flex gap-1.5">
                            <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0s;"></span>
                            <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span>
                            <span class="w-1.5 h-1.5 bg-gray-300 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span>
                        </div>
                    </div>

                    <div class="w-48 h-0.5 bg-gray-700/50 rounded-full overflow-hidden">
                        <div class="h-full bg-gray-300 rounded-full animate-[progress_2s_ease-in-out_infinite]"></div>
                    </div>
                </div>

                <style>
                    @keyframes progress {
                        0% { width: 0%; margin-left: 0%; }
                        50% { width: 70%; margin-left: 15%; }
                        100% { width: 100%; margin-left: 0%; }
                    }
                </style>
            </div>

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
        </div>

        @fluxScripts
        <livewire:diagram.table-side-editor/>
        <livewire:diagram.table-relationships-side-editor/>
    </body>
</html>


