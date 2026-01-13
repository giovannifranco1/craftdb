<div
    x-data="{
        searchFocused: false,
    }"
    @keydown.meta.k.window="searchFocused = true;"
    @keydown.ctrl.k.window="searchFocused = true;"
    @keydown.escape.window="searchFocused = false; $wire.resetSearch()"
>
    <div class="flex items-center mb-5">
        <h2 class="font-sans me-auto text-2xl">{{ __('My Diagrams') }}</h2>

        <div class="mr-3">
            <flux:select wire:model.live="orderByDesc" size="sm">
                <option value="0">{{ __('Newest First') }}</option>
                <option value="1">{{ __('Oldest First') }}</option>
            </flux:select>
        </div>

        <div class="w-80 mr-7">
            <flux:input x-ref="searchInput" x-trap="searchFocused" wire:model.live.debounce.350ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Search for a diagram') }}" kbd="⌘K"/>
        </div>
    </div>

    <div class="flex flex-col h-[40.2rem]">
        <div class="flex-1 overflow-y-auto">
            <div class="h-full grid lg:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($this->getDiagrams as $diagram)
                    <x-diagram.card wire:key="diagram-card-{{ $diagram->id }}" :diagram="$diagram" />
                @empty
                    <div class= "col-span-1 lg:col-span-3 w-full h-full flex flex-col items-center justify-center gap-6 px-4">
                        <div class="flex flex-col items-center gap-4">
                            <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-6">
                                <img src="{{ Vite::asset('resources/images/craftdb_logo_transparent_dark.png') }}" alt="CraftDB Logo" class="size-15 text-zinc-400 dark:text-zinc-500" />
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
                            class="cursor-pointer"
                            variant="primary"
                            size="sm"
                        >
                            {{ __('New Diagram') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>
        </div>
        @if($this->getDiagrams->hasPages())
            <div class="mt-5 flex-shrink-0 mr-7">
                {{ $this->getDiagrams->links() }}
            </div>
        @endif
    </div>

    <!-- Modal para editar diagrama -->
    @foreach($this->getDiagrams as $diagram)
        <livewire:diagram.diagram-edit :$diagram :key="'edit-' . $diagram->id" wire:key="diagram-edit-{{ $diagram->id }}" />
    @endforeach
</div>

