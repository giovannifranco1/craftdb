<!DOCTYPE html>
<html id="canvas-html" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('_partials.head-canvas')
    </head>

    <body>
        @persist('toast')
        <x-ui.toast/>
        @endpersist

        <div x-ref="canvasRoot" x-data="runCanvas(25, @js($isInitialLoad))" diagram-id="{{ $diagram->id }}" diagram-slug="{{ $diagram->slug }}" team-id="{{ $diagram->team_id }}" class="container hs-dropdown [--trigger:contextmenu] pt-12 pb-8">
            <x-ui.context-menu/>
            <x-diagram.canvas.ui.loading/>
            <x-diagram.canvas.ui.controls :diagram="$diagram"/>
            <x-diagram.canvas.ui.topbar :diagram="$diagram"/>

            <canvas id="canvas" class="hs-dropdown-toggle"></canvas>

            <svg x-data="runSvgLines(@js($relationships))" id="svg-overlay" class="z-10">
                <g id="lines-layer"></g>
            </svg>

            <div class="tables z-10">
                {{ $slot }}
            </div>

            <div class="areas z-5">
                @isset($areas) {{ $areas }} @endisset
            </div>

            <div class="notes z-15">
                @isset($notes) {{ $notes }} @endisset
            </div>
        </div>

        <livewire:diagram.canvas.overlays.spotlight :$diagram/>
        <livewire:diagram.canvas.overlays.side-editor :$diagram/>
        <livewire:diagram.canvas.overlays.import :$diagram/>
        <livewire:diagram.canvas.ui.limit :$diagram/>
        <livewire:diagram.ui.graph :$diagram/>
        <livewire:ui.contact-us/>
        @fluxScripts
    </body>
</html>


