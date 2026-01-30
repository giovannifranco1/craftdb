<x-layouts.app.master :title="$title ?? null">
    <x-flux::main style="padding-bottom: 0px;">
        <div class="flex items-start p-0">
            <aside class="p-0">
                @isset($aside) {{ $aside }}
                @elseif(request()->is('settings/*'))
                @else <x-ui.menu :diagramsCount="$diagramsCount ?? 0" />
                @endisset
            </aside>

            <main class="flex-1 justify-between flex-wrap gap-1 gap-y-5">
                {{ $slot }}
            </main>
        </div>
    </x-flux::main>
</x-layouts.app.master>
