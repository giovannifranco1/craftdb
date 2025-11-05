<section
    x-data="{ open: @entangle('open') }"
    x-init="$watch('open', value => value && $dispatch('table-side-editor-opened'))"
    @keydown.window.escape="$event.stopImmediatePropagation(); open = false"
    x-show="open"
    x-cloak
    class="relative z-10"
    aria-labelledby="slide-over-title"
    x-ref="dialog"
    aria-modal="true"
>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="ease-in-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-250"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-200/50 transition-opacity"
    ></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transform transition ease-in-out duration-250 sm:duration-500"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-250 sm:duration-500"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-2xl"
                >
                    <div class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl">
                        <header class="px-4 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900" id="table-title">
                                    {{ ucfirst($table->name) }}
                                </h2>
                                <div class="ml-3 flex h-7 items-center">
                                    <button
                                        type="button"
                                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        @click.stop="open = false"
                                    >
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </header>
                        <article class="relative mt-6 flex-1 px-4 sm:px-6">
                            @use('App\Support\Helpers')
                            @if($table?->id)
                                <div class="space-y-1.5 overflow-y-scroll h-160">
                                    @forelse($this->getColumns() as $column)
                                        <div class="bg-white rounded border border-gray-200 p-2 hover:border-gray-300 transition-all">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                {!! Helpers::getIconDiamondByTypeColumn($column) !!}
                                                <span class="text-xs font-medium text-gray-900">{{ $column->name }}</span>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[7pt] font-medium bg-gray-100 text-gray-800">
                                                    {{ $column->length > 0 ? "$column->type($column->length)" : $column->type }}
                                                </span>

                                                @if($column->is_primary)
                                                    <span class="inline-flex items-center px-1 py-0.5 rounded text-[7pt] font-medium bg-amber-100 text-amber-800">PK</span>
                                                @endif
                                                @if($column->is_foreign)
                                                    <span class="inline-flex items-center px-1 py-0.5 rounded text-[7pt] font-medium bg-red-100 text-red-800">FK</span>
                                                @endif
                                                @if($column->is_nullable)
                                                    <span class="inline-flex items-center px-1 py-0.5 rounded text-[7pt] font-medium bg-blue-100 text-blue-800">NULL</span>
                                                @endif
                                                @if($column->default_value)
                                                    <span class="inline-flex items-center px-1 py-0.5 rounded text-[7pt] font-medium bg-green-100 text-green-800" title="Default: {{ $column->default_value }}">
                                                        Default
                                                    </span>
                                                @endif
                                            </div>

                                            @if($column->comment)
                                                <p class="text-xs text-gray-500 italic mt-0.5">{{ $column->comment }}</p>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                                            <p class="text-sm text-gray-500">No columns found</p>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="flex justify-end mb-5">
                                    <flux:button @click.stop="$dispatch('table-relationships-side-editor.open', { table: {{ $table->id ?? '' }} })" variant="primary">
                                        Table Side Relationships
                                    </flux:button>
                                </div>
                            @endif
                        </article>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
