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
                                            class="w-3 h-3 bg-[var(--table-base-bg)] rounded-full shadow-sm"
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
                                        Columns<span class="hs-tab-active:bg-[var(--table-base-bg)]/20 hs-tab-active:text-[var(--table-base-bg)] dark:hs-tab-active:bg-blue-800 dark:hs-tab-active:text-white ms-1 py-0.5 px-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-300">{{ $this->getColumns()?->count() }}</span>
                                    </button>
                                    <button type="button" class="hs-tab-active:font-semibold hs-tab-active:border-[var(--table-base-bg)] hs-tab-active:text-[var(--table-base-bg)] py-4 px-1 inline-flex items-center gap-x-2 border-b-2 border-transparent text-sm whitespace-nowrap text-gray-500 hover:text-[var(--table-base-bg)] focus:outline-hidden focus:text-[var(--table-base-bg)] disabled:opacity-50 disabled:pointer-events-none dark:text-neutral-400 dark:hover:text-blue-500" id="tabs-with-badges-item-2" aria-selected="false" data-hs-tab="#tab-foreign-keys" aria-controls="tab-foreign-keys" role="tab">
                                        Foreign Keys<span class="hs-tab-active:bg-[var(--table-base-bg)]/20 hs-tab-active:text-[var(--table-base-bg)] dark:hs-tab-active:bg-blue-800 dark:hs-tab-active:text-white ms-1 py-0.5 px-1.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-neutral-700 dark:text-neutral-300">{{ $this->getForeignKeys()?->count() }}</span>
                                    </button>
                                </nav>
                            </div>

                            <div class="mt-3">

                                <div id="tab-columns" role="tabpanel" aria-labelledby="tabs-with-badges-item-1">

                                    <div class="mb-3 flex justify-end">
                                        <flux:button icon="plus-circle" size="sm" wire:click="addColumn()" variant="primary">
                                            Adicionar Coluna
                                        </flux:button>
                                    </div>

                                    <div class="space-y-2 overflow-y-scroll h-150">
                                        @forelse($this->getColumns() as $column)
                                            <div
                                                x-data="{ open: false }"
                                                @class([
                                                    'bg-white border-l-4 rounded-lg border shadow-sm transition-all overflow-hidden',
                                                    'border-gray-200 hover:border-gray-300 hover:shadow-md' => $selectedColumnId !== $column->id,
                                                    'border-blue-500 ring-2 ring-blue-200 shadow-md' => $selectedColumnId === $column->id,
                                                ])
                                            >
                                                <!-- Header do Collapse -->
                                                <div
                                                    @class([
                                                        'flex items-center justify-between px-4 py-3 cursor-pointer transition-all',
                                                        'bg-gradient-to-r from-gray-50 to-white hover:from-gray-100 hover:to-gray-50' => $selectedColumnId !== $column->id,
                                                        'bg-gradient-to-r from-blue-50 to-blue-100/50' => $selectedColumnId === $column->id,
                                                    ])
                                                    @click="open = !open"
                                                >
                                                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                                        <div class="flex-shrink-0">
                                                            {!! Helpers::getIconDiamondByTypeColumn($column) !!}
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <span
                                                                    class="text-xs font-semibold text-gray-900 font-[Poppins] cursor-pointer hover:text-blue-600 truncate transition-colors flex-1 min-w-0"
                                                                    title="Clique para editar o nome"
                                                                >{{ $column->name }}
                                                                </span>
                                                                <span class="text-[10px] text-gray-500 font-mono bg-gray-100 px-1.5 py-0.5 rounded flex-shrink-0">
                                                                    {{ $column->length > 0 ? "$column->type($column->length)" : $column->type }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Checkboxes compactos -->
                                                    <flux:fieldset wire:click.stop class="text-xs">
                                                        <div class="flex gap-2 *:gap-x-1">
                                                            <flux:checkbox value="pk" label="pk" />
                                                            <flux:checkbox value="fk" label="fk" />
                                                            <flux:checkbox value="nullable" label="n" />
                                                        </div>
                                                    </flux:fieldset>

                                                    <!-- Ícone de collapse -->
                                                    <svg
                                                        class="w-5 h-5 text-gray-400 ml-3 flex-shrink-0 transition-transform duration-300"
                                                        :class="{ 'rotate-180': open }"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>

                                                <!-- Conteúdo do Collapse com Formulário -->
                                                <div
                                                    x-show="open"
                                                    x-collapse
                                                    class="overflow-hidden"
                                                >
                                                    <div class="px-4 pb-4 pt-3 border-t border-gray-200 bg-gray-50/50 space-y-4">
                                                        @if($selectedColumnId === $column->id)
                                                            <!-- Formulário de Edição -->
                                                            <form wire:submit.prevent="updateColumn({{ $column->id }})" class="space-y-3">
                                                                <!-- Nome da Coluna -->
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nome</label>
                                                                    <input
                                                                        type="text"
                                                                        wire:model="columnName"
                                                                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                        placeholder="Nome da coluna"
                                                                    />
                                                                    @error('columnName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                                </div>

                                                                <!-- Tipo e Length -->
                                                                <div class="grid grid-cols-2 gap-3">
                                                                    <div>
                                                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Tipo</label>
                                                                        <select
                                                                            wire:model="columnType"
                                                                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                        >
                                                                            @foreach($this->getColumnTypes() as $type => $label)
                                                                                <option value="{{ $type }}">{{ $label }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('columnType') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Length</label>
                                                                        <input
                                                                            type="number"
                                                                            wire:model="columnLength"
                                                                            min="0"
                                                                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                            placeholder="Ex: 255"
                                                                        />
                                                                        @error('columnLength') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                                    </div>
                                                                </div>

                                                                <!-- Default Value -->
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Valor Padrão</label>
                                                                    <input
                                                                        type="text"
                                                                        wire:model="columnDefaultValue"
                                                                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                                        placeholder="Ex: 'default' ou NULL"
                                                                    />
                                                                    @error('columnDefaultValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                                </div>

                                                                <!-- Comment -->
                                                                <div>
                                                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Comentário</label>
                                                                    <textarea
                                                                        wire:model="columnComment"
                                                                        rows="2"
                                                                        class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                                                        placeholder="Descrição da coluna..."
                                                                    ></textarea>
                                                                    @error('columnComment') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                                                </div>

                                                                <!-- Botões de Ação -->
                                                                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200">
                                                                    <button
                                                                        type="button"
                                                                        wire:click="selectColumn({{ $column->id }})"
                                                                        class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                                                    >
                                                                        Cancelar
                                                                    </button>
                                                                    <button
                                                                        type="submit"
                                                                        class="px-4 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                                    >
                                                                        Salvar
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        @else
                                                            <!-- Visualização dos Detalhes -->
                                                            <div class="space-y-2.5">
                                                                @if($column->default_value)
                                                                    <div class="flex items-start gap-2 text-xs">
                                                                        <span class="font-semibold text-gray-600 min-w-[60px]">Default:</span>
                                                                        <span class="text-gray-900 font-mono bg-white px-2 py-1 rounded border border-gray-200">{{ $column->default_value }}</span>
                                                                    </div>
                                                                @endif
                                                                @if($column->comment)
                                                                    <div class="flex items-start gap-2 text-xs">
                                                                        <span class="font-semibold text-gray-600 min-w-[60px]">Comment:</span>
                                                                        <span class="text-gray-700 flex-1 bg-white px-2 py-1 rounded border border-gray-200">{{ $column->comment }}</span>
                                                                    </div>
                                                                @endif
                                                                @if(!$column->default_value && !$column->comment)
                                                                    <div class="text-center py-3">
                                                                        <p class="text-xs text-gray-400">Nenhum detalhe adicional</p>
                                                                        <button
                                                                            type="button"
                                                                            wire:click.stop="selectColumn({{ $column->id }})"
                                                                            class="mt-2 text-xs text-blue-600 hover:text-blue-700 font-medium"
                                                                        >
                                                                            Editar coluna
                                                                        </button>
                                                                    </div>
                                                                @else
                                                                    <div class="pt-1">
                                                                        <button
                                                                            type="button"
                                                                            wire:click.stop="selectColumn({{ $column->id }})"
                                                                            class="w-full text-xs px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium"
                                                                        >
                                                                            Editar Detalhes
                                                                        </button>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="bg-white rounded-lg h-150 flex justify-center items-center border border-gray-200 p-8 text-center">
                                                <p class="text-sm text-gray-500">No columns found</p>
                                            </div>
                                        @endforelse
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
