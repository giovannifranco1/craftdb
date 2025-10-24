<div>
    <div class="flex items-center mb-5">
        <h2 class="font-sans me-auto text-2xl">My Diagrams</h2>
        <div>
            <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" size="sm" placeholder="Nome do diagrama"  kbd="⌘K" />
        </div>
    </div>

    <div class="flex items-start">
        <div class="w-64 shrink-0 pb-4 me-10">
            <flux:navlist>
                <flux:navlist.item href="#" badge="{{ $this->getDiagrams()->count() }}" current>Diagrams</flux:navlist.item>
                <flux:navlist.item href="#">Billing</flux:navlist.item>
                <flux:navlist.item href="#">Configuration</flux:navlist.item>
            </flux:navlist>
        </div>

        <div class="flex-1 flex justify-between flex-wrap gap-1 gap-y-5">
            @foreach($this->getDiagrams() as $diagram)
                <x-diagram.card :diagram="$diagram" />
            @endforeach
        </div>
    </div>

    <div class="mt-5">
        {{ $this->getDiagrams()->links() }}
    </div>
</div>
