<div>
    <div class="flex items-center mb-5">
        <h2 class="font-sans me-auto text-2xl">{{ __('My Diagrams') }}</h2>

        <div class="w-80 mr-7">
            <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Search for a diagram') }}" kbd="⌘K"/>
        </div>
    </div>

    <div class="flex flex-col h-[37rem]">
        <div class="flex-1 overflow-y-auto">
            <div class="h-full grid lg:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($this->getDiagrams() as $diagram)
                    <x-diagram.card wire:key="diagram-card-{{ $diagram->id }}" :diagram="$diagram" />
                @empty
                    <div class="col-span-1 lg:col-span-3 w-full h-full flex flex-col items-center justify-center gap-6 px-4">
                        <div class="flex flex-col items-center gap-4">
                            <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-6">
                                <flux:icon.layout-grid class="size-12 text-zinc-400 dark:text-zinc-500" />
                            </div>
                            @if($this->favorites)
                                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                    {{ __('No favorites found') }}
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    {{ __('Get started by adding your first favorite diagram.') }}
                                </flux:text>
                            @else
                                <flux:heading size="lg" class="text-zinc-900 dark:text-zinc-100">
                                    {{ __('No diagrams found') }}
                                </flux:heading>
                                <flux:text class="text-zinc-600 dark:text-zinc-400">
                                    {{ __('Get started by creating your first database diagram.') }}
                                </flux:text>
                            @endif
                        </div>
                        <flux:button
                            @click="$flux.modal('diagram-create').show()"
                            icon="plus"
                            variant="primary"
                            size="sm"
                        >
                            {{ __('Create Diagram') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>
        </div>
        @if($this->getDiagrams()->hasPages())
            <div class="mt-5 flex-shrink-0 mr-7">
                {{ $this->getDiagrams()->links() }}
            </div>
        @endif
    </div>
</div>
