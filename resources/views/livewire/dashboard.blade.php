<x-slot:title>Dashboard</x-slot:title>

<div>
    <div class="flex items-center mb-5">
        <h2 class="font-sans me-auto text-2xl">My Diagrams</h2>

        <div class="w-80">
            <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" size="sm" placeholder="Nome do diagrama"  kbd="⌘K" />
        </div>
    </div>

    <div class="flex-1 flex justify-between flex-wrap gap-1 gap-y-5">
        @foreach($this->getDiagrams() as $diagram)
            <x-diagram.card wire:key="diagram-card-{{ $diagram->id }}" :diagram="$diagram" />
        @endforeach
    </div>

    <div class="mt-5">
            {{ $this->getDiagrams()->links() }}
    </div>
</div>
