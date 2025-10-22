<div>
    <div class="flex items-center mb-5">
        <h2 class="font-bold me-auto text-2xl">Meus Diagramas</h2>
        <div>
            <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" size="sm" placeholder="Nome do diagrama"  kbd="⌘K" />
        </div>
    </div>

    <div class="flex justify-between flex-wrap gap-10">
        @foreach($this->getDiagrams() as $diagram)
            <x-diagram.card :diagram="$diagram" />
        @endforeach
    </div>

    <div class="mt-5">
        {{ $this->getDiagrams()->links() }}
    </div>
</div>
