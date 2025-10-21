<x-layouts.app.sidebar :title="$title ?? null">
    <x-flux::main>
        {{ $slot }}
    </x-flux::main>
</x-layouts.app.sidebar>
