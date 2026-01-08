<!DOCTYPE html>
<html id="canvas-html" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head-canvas')
    </head>

    <body>
        @persist('toast')
        <x-ui.toast/>
        @endpersist

        <div x-ref="canvasRoot" x-data="runCanvas(25, @js($isInitialLoad))" diagram-id="{{ $diagram->id }}" diagram-slug="{{ $diagram->slug }}" team-id="{{ $diagram->team_id }}" class="container hs-dropdown [--trigger:contextmenu] pt-12 pb-8">
            <x-ui.context-menu/>
            <x-ui.canvas.loading/>
            <x-ui.canvas.controls/>
            <x-ui.canvas.topbar :diagram="$diagram"/>

            <canvas id="canvas" class="hs-dropdown-toggle"></canvas>

            <svg x-data="runSvgLines(@js($relationships))" id="svg-overlay" class="z-10">
                <g id="lines-layer"></g>
            </svg>

            <div class="tables z-10">
                {{ $slot }}
            </div>

            <div class="areas z-5">
                {{ $areas }}
            </div>

            <div class="notes z-15">
                {{ $notes }}
            </div>
        </div>

        <livewire:diagram.spotlight :$diagram/>
        <livewire:diagram.graph :$diagram/>
        <livewire:diagram.table-side-editor/>

        @fluxScripts
    </body>
</html>


