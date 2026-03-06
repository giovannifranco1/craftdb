<x-layouts.app.master :title="$title ?? null">
    <x-flux::main style="padding-bottom: 0px;">
        <div class="flex items-stretch gap-8 w-full h-full min-h-0">
            <aside class="shrink-0">
                @isset($aside) {{ $aside }}
                @elseif(request()->is('settings/*'))
                @else <x-ui.menu :diagramsCount="$diagramsCount ?? 0" />
                @endisset
            </aside>

            <main class="flex-1 bg-white border border-zinc-200 dark:border-zinc-700 dark:bg-background-dark rounded-xl p-6 w-full flex-col min-w-0 overflow-auto">
                {{ $slot }}
            </main>
        </div>
    </x-flux::main>
</x-layouts.app.master>
