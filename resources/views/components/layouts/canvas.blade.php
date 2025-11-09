<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head-canvas')
    </head>

    <body>
        <x-ui.alert/>

        <div diagram-id="{{ $diagram->id }}" x-data="runCanvas()" class="container hs-dropdown [--trigger:contextmenu]">
            <x-ui.context-menu/>
            <x-ui.canvas.loading/>
            <x-ui.canvas.controls/>

            <canvas id="canvas" class="hs-dropdown-toggle"></canvas>

            <svg x-data="runSvgLines(@js($relationships))" id="svg-overlay">
                <g id="lines-layer"></g>
            </svg>

            <div class="tables">
                {{ $slot }}
            </div>
        </div>

        @fluxScripts
        <livewire:diagram.table-side-editor/>
        <livewire:diagram.table-relationships-side-editor/>
    </body>
</html>


