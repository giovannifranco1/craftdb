<x-layouts.app :title="__('Dashboard')">

    <div class="flex items-center mb-5">
        <h2 class="font-bold me-auto text-2xl">Meus Diagramas</h2>
        <div>
            <flux:input icon="magnifying-glass" size="sm" placeholder="Filter by..."  kbd="⌘K" />
        </div>
    </div>

    <div class="flex justify-between flex-wrap gap-10">
        @foreach($diagrams as $diagram)
            <x-diagram.card :diagram="$diagram" />
        @endforeach
    </div>

    <div class="mt-5">
        {{ $diagrams->links() }}
    </div>
</x-layouts.app>
