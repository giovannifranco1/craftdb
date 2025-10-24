<x-layouts.app.master :title="$title ?? null">
    <x-flux::main>
        {{ $slot }}
    </x-flux::main>
</x-layouts.app.master>
