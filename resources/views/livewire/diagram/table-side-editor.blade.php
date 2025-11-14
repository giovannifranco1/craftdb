<section
    x-data="{ open: @entangle('open') }"
    x-show="open"
    x-cloak
    x-ref="dialog"
    class="relative z-10"
    aria-labelledby="slide-over-title"
    aria-modal="true"
    @keydown.window.escape="$event.stopImmediatePropagation(); open = false; $dispatch('table-side-editor::closed')"
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
        class="fixed inset-0 transition-opacity pointer-events-none"
    ></div>

    <div class="fixed inset-0 overflow-hidden pointer-events-none" table-id="{{ $table->id }}">
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
                    class="pointer-events-auto w-screen bg-white max-w-[28rem]"
                >
                    <div
                        wire:loading.class.delay.shorter="opacity-60"
                        class="flex h-full flex-col overflow-y-scroll bg-white py-6 shadow-xl"
                    >
                        <header class="px-4 sm:px-6">
                            <div class="flex items-start justify-between">
                                <div class="flex gap-2">
                                    <h2 class="text-lg font-medium text-gray-900 font-[Poppins]" id="table-title">
                                        {{ ucfirst($table->name) }}
                                    </h2>
                                    <div class="flex items-center">
                                        <div
                                            class="w-4 h-4 bg-[var(--table-base-bg)] rounded-full shadow-sm"
                                            title="table color: {{ $table->color ?? \App\Models\Table::COLOR_DEFAULT }}"
                                        ></div>
                                    </div>
                                </div>
                                <div class="ml-3 flex h-7 items-center">
                                    <button
                                        type="button"
                                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        @click.stop="open = false; $dispatch('table-side-editor::closed')"
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

                            <div class="border-b border-gray-200 dark:border-neutral-700">
                                <nav class="flex gap-x-1" aria-label="Tabs" role="tablist" aria-orientation="horizontal">
                                    <button type="button" class="hs-tab-active:font-semibold hs-tab-active:border-[var(--table-base-bg)] hs-tab-active:text-[var(--table-base-bg)] py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-[var(--table-base-bg)] focus:outline-hidden focus:text-[var(--table-base-bg)] disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500 active" id="tabs-with-badges-item-1" aria-selected="true" data-hs-tab="#tab-columns" aria-controls="tab-columns" role="tab">
                                        Columns<span class="hs-tab-active:bg-[var(--table-base-bg)]/20 hs-tab-active:text-[var(--table-base-bg)] dark:hs-tab-active:bg-blue-800 dark:hs-tab-active:text-white ms-1 py-0.5 px-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-300">{{ $this->getColumns()->count() }}</span>
                                    </button>
                                    <button type="button" class="hs-tab-active:font-semibold hs-tab-active:border-[var(--table-base-bg)] hs-tab-active:text-[var(--table-base-bg)] py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-[var(--table-base-bg)] focus:outline-hidden focus:text-[var(--table-base-bg)] disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500" id="tabs-with-badges-item-2" aria-selected="false" data-hs-tab="#tab-foreign-keys" aria-controls="tab-foreign-keys" role="tab">
                                        Foreign Keys<span class="hs-tab-active:bg-[var(--table-base-bg)]/20 hs-tab-active:text-[var(--table-base-bg)] dark:hs-tab-active:bg-blue-800 dark:hs-tab-active:text-white ms-1 py-0.5 px-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-300">{{ $this->getForeignKeys()->count() }}</span>
                                    </button>
                                </nav>
                            </div>

                            <div class="mt-3">
                                <div id="tab-columns" role="tabpanel" aria-labelledby="tabs-with-badges-item-1">

                                    <div class="mb-3 flex justify-end">
                                        <flux:button icon="plus-circle" size="sm" wire:click="openCreateColumnModal" variant="primary">
                                            Adicionar Coluna
                                        </flux:button>
                                    </div>

                                    <div class="space-y-1.5 overflow-y-scroll h-89.5">
                                        @forelse($this->getColumns() as $column)
                                            <div
                                                @class([
                                                    'bg-white border-l-4 rounded-l-md rounded border p-2 transition-all flex gap-2 justify-between',
                                                    'border-gray-200 hover:border-gray-300' => $selectedColumnId !== $column->id,
                                                    'border-blue-300 table-column-border-left ring-1 table-column-border-ring' => $selectedColumnId === $column->id,
                                                ])
                                                wire:click="selectColumn({{ $column->id }})"
                                            >
                                                <div class="flex items-center gap-1.5 mb-1 flex-1">
                                                    {!! Helpers::getIconDiamondByTypeColumn($column) !!}
                                                    @if($editingColumnId === $column->id)
                                                        <div class="flex items-center gap-1 flex-1" @click.stop>
                                                            <flux:input
                                                                wire:model="editingColumnName"
                                                                wire:keydown.enter="updateColumnName({{ $column->id }})"
                                                                wire:keydown.escape="cancelEditingColumnName"
                                                                class="text-[9pt] font-medium font-[Poppins]"
                                                                style="all: unset; height: 10px;"
                                                                autofocus
                                                            />
                                                            <flux:icon
                                                                wire:click="updateColumnName({{ $column->id }})"
                                                                name="check"
                                                                class="size-3 block"
                                                            />
                                                            <flux:icon
                                                                wire:click="cancelEditingColumnName"
                                                                name="x-mark"
                                                                class="size-3 block"
                                                            />
                                                        </div>
                                                    @else
                                                        <span
                                                            class="text-xs font-medium text-gray-900 font-[Poppins] cursor-pointer hover:text-blue-600"
                                                            wire:click.stop="startEditingColumnName({{ $column->id }})"
                                                            title="Clique para editar o nome"
                                                        >{{ $column->name }}</span>
                                                    @endif
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
                                            </div>
                                        @empty
                                            <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                                                <p class="text-sm text-gray-500">No columns found</p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="relative flex items-center my-3">
                                        <div class="flex-grow border-t border-gray-200"></div>
                                        <span class="mx-3 text-[10px] uppercase tracking-wide text-gray-400">Detalhes</span>
                                        <div class="flex-grow border-t border-gray-200"></div>
                                    </div>

                                    <div class="h-70 relative">
                                        @php($selected = $this->getSelectedColumn())
                                        @if($selected)
                                            <div wire:loading.delay class="absolute inset-0 backdrop-blur-sm rounded-lg z-10">
                                                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-8 h-8">
                                                    <div class="absolute inset-0 rounded-full border-2 border-gray-300/30 border-t-[var(--table-base-bg)] animate-spin"></div>
                                                </div>
                                            </div>
                                            <div class="bg-white table-column-border-left rounded-lg border-l-4 border border-gray-200 p-4 shadow-sm h-full">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-center gap-2">
                                                        {!! Helpers::getIconDiamondByTypeColumn($selected) !!}
                                                        <h3 class="text-sm font-semibold text-gray-900">{{ $selected->name }}</h3>
                                                    </div>
                                                    <div class="flex flex-wrap items-center gap-1">
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[7pt] font-medium bg-gray-100 text-gray-800">
                                                            {{ $selected->length > 0 ? "$selected->type($selected->length)" : $selected->type }}
                                                        </span>
                                                        @if($selected->is_primary)
                                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-[7pt] font-medium bg-amber-100 text-amber-800">PK</span>
                                                        @endif
                                                        @if($selected->is_foreign)
                                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-[7pt] font-medium bg-red-100 text-red-800">FK</span>
                                                        @endif
                                                        @if($selected->is_nullable)
                                                            <span class="inline-flex items-center px-1 py-0.5 rounded text-[7pt] font-medium bg-blue-100 text-blue-800">NULL</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                                    <div class="space-y-0.5">
                                                        <dt class="text-gray-500">Type</dt>
                                                        <dd class="text-gray-900 font-medium">{{ $selected->type }}</dd>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <dt class="text-gray-500">Length</dt>
                                                        <dd class="text-gray-900 font-medium">{{ $selected->length ?: '—' }}</dd>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <dt class="text-gray-500">Nullable</dt>
                                                        <dd class="text-gray-900 font-medium">{{ $selected->is_nullable ? 'Yes' : 'No' }}</dd>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <dt class="text-gray-500">Primary</dt>
                                                        <dd class="text-gray-900 font-medium">{{ $selected->is_primary ? 'Yes' : 'No' }}</dd>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <dt class="text-gray-500">Foreign</dt>
                                                        <dd class="text-gray-900 font-medium">{{ $selected->is_foreign ? 'Yes' : 'No' }}</dd>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <dt class="text-gray-500">Default</dt>
                                                        <dd class="text-gray-900 font-medium">{{ $selected->default_value ?? '—' }}</dd>
                                                    </div>
                                                </dl>

                                                <dt class="text-xs text-gray-500 mb-2 mt-4">Comment</dt>

                                                <div class="grid gap-2 grid-cols-6 items-start">
                                                    <div class="col-span-5">
                                                        <div class="rounded-md border border-gray-200 bg-gray-50 px-3 py-1 min-h-12 max-h-24 overflow-y-auto">
                                                            <p class="text-[11px] leading-5 text-gray-700">{{ $selected->comment ?: '—' }}</p>
                                                        </div>
                                                    </div>

                                                    <div class="flex col-auto flex-col gap-1 justify-start">
                                                        <flux:button size="sm" wire:click="openEditColumnModal({{ $selected->id }})" variant="ghost" class="text-xs">
                                                            <flux:icon.pencil class="size-3" />
                                                        </flux:button>
                                                        <flux:button size="sm" wire:click="deleteColumn" wire:confirm="Tem certeza que deseja deletar esta coluna?" variant="ghost" class="text-xs text-red-600 hover:text-red-700">
                                                            <flux:icon.trash class="size-3" />
                                                        </flux:button>
                                                        <flux:button size="sm" wire:click="clearSelection" variant="ghost" class="text-xs">
                                                            <flux:icon.x-mark class="size-3" />
                                                        </flux:button>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="bg-gray-50 rounded-lg border border-l-4 border-dashed border-gray-200 p-6 h-full flex items-center justify-center">
                                                <p class="text-xs text-gray-500">Selecione uma coluna para ver os detalhes</p>
                                            </div>
                                        @endif
                                    </div>

                                </div>

                                <div x-data="{table: @entangle('table') }" id="tab-foreign-keys" class="hidden" role="tabpanel" aria-labelledby="tabs-with-badges-item-2">
                                    <div class="flex flex-col justify-between h-150">
                                        <div class="space-y-1.5 overflow-y-scroll">
                                            @forelse($this->getForeignKeys() as $column)
                                                <div class="bg-white rounded border border-gray-200 p-2 hover:border-gray-300 transition-all">
                                                    <div class="flex items-center gap-1.5 mb-1">
                                                        {!! Helpers::getIconDiamondByTypeColumn($column) !!}
                                                        <span class="text-xs font-medium text-gray-900">{{ $column->name }}</span>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
                                                    <p class="text-sm text-gray-500">No foreign keys found</p>
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="flex justify-end mt-5">
                                            <flux:button  variant="primary">
                                                Relationships
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

            </div>
        </div>
    </div>

</section>
