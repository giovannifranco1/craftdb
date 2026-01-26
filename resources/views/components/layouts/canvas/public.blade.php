<!DOCTYPE html>
<html id="canvas-html" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @vite(['resources/css/canvas-public.css'])
        @include('_partials.head-canvas')
    </head>

    <body class="public-view" x-init="$flux.dark = false">
        <div x-ref="canvasRoot" x-data="runCanvas(25, false)" diagram-id="{{ $diagram->id }}" diagram-slug="{{ $diagram->slug }}" team-id="{{ $diagram->team_id }}" class="container pt-20 pb-8">
            <x-diagram.public.canvas.ui.header :$diagram/>
            <canvas id="canvas"></canvas>

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

        @fluxScripts
    </body>
</html>
