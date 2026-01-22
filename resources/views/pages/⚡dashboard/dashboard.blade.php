<div
    x-data="{
        searchFocused: false,
    }"
    @keydown.meta.k.window="searchFocused = true;"
    @keydown.ctrl.k.window="searchFocused = true;"
    @keydown.escape.window="searchFocused = false; $wire.resetSearch()"
>
    <div class="flex items-center mb-5">
        <flux:heading class="font-normal text-zinc-900 dark:text-zinc-50 me-auto text-2xl">{{ __('My Diagrams') }}</flux:heading>

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
                @forelse($this->diagrams as $diagram)
                    <x-diagram.ui.card wire:key="diagram-card-{{ $diagram->id }}" :diagram="$diagram" />
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
        @if($this->diagrams->hasPages())
            <div class="mt-5 flex-shrink-0 mr-7">
                {{ $this->diagrams->links() }}
            </div>
        @endif
    </div>

    <flux:modal :closable="false" name="confirm-delete-diagram" class="min-w-[25rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to delete this diagram?') }}</flux:heading>
                <flux:text class="mt-2">{{ __('This action will delete the diagram and all its data.') }}</flux:text>
            </div>
        </div>

        <div class="flex gap-2 mt-4">
            <flux:spacer />

            <flux:button x-on:click="$flux.modal('confirm-delete-diagram').close()" type="button" class="cursor-pointer" variant="ghost" icon="x-mark">{{ __('Cancel') }}</flux:button>
            <flux:button wire:click="deleteDiagram" class="cursor-pointer" variant="danger" icon="trash" type="button">{{ __('Delete') }}</flux:button>
        </div>
    </flux:modal>

    <!-- Modal para editar diagrama -->
    @foreach($this->diagrams as $diagram)
        <livewire:diagram.diagram-edit :$diagram :wire:key="$diagram->id" />
    @endforeach
</div>

