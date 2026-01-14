<section
    x-data="{ open: @entangle('open') }"
    x-show="open"
    x-cloak
    x-ref="dialog"
    class="relative z-30"
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
        class="fixed inset-0 transition-opacity pointer-events-none bg-black/20"
    ></div>

    @use('App\Support\Helpers')
    @php
        $table = $this->getTable();
    @endphp
    @if($table)
        <div class="fixed inset-0 overflow-hidden pointer-events-none" table-id="{{ $tableId }}">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-x-0 bottom-0 flex max-h-[85vh] overflow-y-auto">

                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transform transition ease-in-out duration-250 sm:duration-500"
                        x-transition:enter-start="translate-y-full"
                        x-transition:enter-end="translate-y-0"
                        x-transition:leave="transform transition ease-in-out duration-250 sm:duration-500"
                        x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full"
                        class="pointer-events-auto w-full max-h-[85vh] min-h-[35vh]"
                    >
                        <div class="flex flex-col overflow-y-auto bg-white/95 py-2 dark:bg-zinc-900/95 backdrop-blur-md border-t border-zinc-200/70 dark:border-zinc-800/70 shadow-lg shadow-zinc-900/10 rounded-t-2xl min-h-[35vh]">
                            <header class="px-4 sm:px-6 py-2 border-b border-zinc-200/70 dark:border-zinc-800/70">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-4 h-4 bg-[var(--table-base-bg)] rounded-full shadow-sm ring-2 ring-white dark:ring-zinc-900"
                                            title="table color: {{ $table->color ?? \App\Models\Table::COLOR_DEFAULT }}"
                                        ></div>
                                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50 font-[Poppins]" id="table-title">
                                            {{ ucfirst($table->name) }}
                                        </h2>
                                    </div>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                                        @click.stop="open = false; $dispatch('table-side-editor::closed')"
                                    >
                                        <span class="sr-only">Close panel</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </header>

                            <article class="relative flex-1 px-4 sm:px-6 py-1 min-h-[30vh]">

                                <div class="border-b border-zinc-200/70 dark:border-zinc-800/70 mb-2">
                                    <div x-data="{
                                        activeTab: 'columns',
                                        resetToColumnsTab() {
                                            this.activeTab = 'columns';
                                            document.getElementById('tab-columns')?.classList.remove('hidden');
                                            document.getElementById('tab-foreign-keys')?.classList.add('hidden');
                                        }
                                    }"
                                    @table-side-editor::opened.window="resetToColumnsTab()"
                                    class="flex items-center justify-between">
                                        <nav class="flex gap-x-1" aria-label="Tabs" role="tablist" aria-orientation="horizontal">
                                            <button
                                                type="button"
                                                @click="activeTab = 'columns'; document.getElementById('tab-columns').classList.remove('hidden'); document.getElementById('tab-foreign-keys').classList.add('hidden');"
                                                :class="activeTab === 'columns' ? 'font-semibold border-[var(--table-base-bg)] text-[var(--table-base-bg)]' : 'border-zinc-300 dark:border-zinc-600'"
                                                class="py-3 px-2 inline-flex items-center gap-x-2 border-b-2 text-sm whitespace-nowrap text-zinc-500 dark:text-zinc-400 hover:text-[var(--table-base-bg)] focus:outline-none focus:text-[var(--table-base-bg)] disabled:opacity-50 disabled:pointer-events-none transition-colors"
                                                id="tabs-with-badges-item-1"
                                                :aria-selected="activeTab === 'columns' ? 'true' : 'false'"
                                                aria-controls="tab-columns"
                                                role="tab"
                                            >
                                                Columns<span class="ms-1.5 py-0.5 px-2 rounded-full text-xs font-medium" :class="activeTab === 'columns' ? 'bg-[var(--table-base-bg)]/20 text-[var(--table-base-bg)] dark:bg-[var(--table-base-bg)]/30' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300'">{{ $this->getColumns()?->count() }}</span>
                                            </button>
                                            <button
                                                type="button"
                                                x-on:click="activeTab = 'foreign-keys'; document.getElementById('tab-columns').classList.add('hidden'); document.getElementById('tab-foreign-keys').classList.remove('hidden'); $nextTick(() => { setTimeout(() => { const graph = document.getElementById('foreign-keys-graph'); if (graph && graph._x_dataStack && graph._x_dataStack[0]) { const graphData = graph._x_dataStack[0]; if (!graphData.svg || !graphData.svg.node()) { graphData.setupGraph(); } } }, 300); });"
                                                :class="activeTab === 'foreign-keys' ? 'font-semibold border-[var(--table-base-bg)] text-[var(--table-base-bg)]' : 'border-zinc-300 dark:border-zinc-600'"
                                                class="py-3 px-2 inline-flex items-center gap-x-2 border-b-2 text-sm whitespace-nowrap text-zinc-500 dark:text-zinc-400 hover:text-[var(--table-base-bg)] focus:outline-none focus:text-[var(--table-base-bg)] disabled:opacity-50 disabled:pointer-events-none transition-colors"
                                                id="tabs-with-badges-item-2"
                                                :aria-selected="activeTab === 'foreign-keys' ? 'true' : 'false'"
                                                aria-controls="tab-foreign-keys"
                                                role="tab"
                                            >
                                                Foreign Keys<span class="ms-1.5 py-0.5 px-2 rounded-full text-xs font-medium" :class="activeTab === 'foreign-keys' ? 'bg-[var(--table-base-bg)]/20 text-[var(--table-base-bg)] dark:bg-[var(--table-base-bg)]/30' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300'">{{ $this->getForeignKeys()?->count() }}</span>
                                            </button>
                                        </nav>
                                        <div x-show="activeTab === 'columns'" x-cloak>
                                            <flux:button icon="plus-circle" size="sm" wire:click="addColumn()" variant="primary">
                                                Adicionar Coluna
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>

                                <div>

                                    <div
                                        id="tab-columns"
                                        role="tabpanel"
                                        aria-labelledby="tabs-with-badges-item-1"
                                        @table-side-editor::column-reordered.window="$wire.$refresh()"
                                        x-data="{ isEventUpdateColumn: 0 }"
                                    >
                                        <div class="grid grid-cols-[600px_1fr] gap-4 h-[45vh]">
                                            <!-- Lista de Colunas (Esquerda) -->
                                            <div class="space-y-1 overflow-y-auto pr-2 h-full">
                                                @forelse($this->getColumns() as $column)
                                                    <div
                                                        @class([
                                                            'bg-white dark:bg-zinc-800/50 rounded-md border transition-all',
                                                            'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600' => $selectedColumnId !== $column->id,
                                                            'border-[var(--table-base-bg)] ring-1 ring-[var(--table-base-bg)]/30 bg-[var(--table-base-bg)]/5 dark:bg-[var(--table-base-bg)]/10' => $selectedColumnId === $column->id,
                                                        ])
                                                    >
                                                        <div class="flex items-center justify-between px-2.5 py-0.5">
                                                            <div
                                                                x-on:click="$wire.selectColumn({{ $column->id }}); isEventUpdateColumn++;"
                                                                class="flex items-center gap-1.5 flex-1 min-w-0 cursor-pointer"
                                                            >
                                                                <div class="flex-shrink-0">
                                                                    {!! Helpers::getIconDiamondByTypeColumn($column) !!}
                                                                </div>
                                                                <span
                                                                    class="text-xs font-medium text-zinc-900 dark:text-zinc-100 truncate flex-1 min-w-0"
                                                                    title="{{ $column->name }}"
                                                                >{{ $column->name }}</span>
                                                                <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-mono bg-zinc-100 dark:bg-zinc-700 px-1 py-0.5 rounded flex-shrink-0">
                                                                    {{ $column->length > 0 ? "$column->type($column->length)" : $column->type }}
                                                                </span>
                                                            </div>
                                                            <!-- Checkboxes e ações -->
                                                            <div class="flex items-center gap-1.5 ml-2" wire:click.stop>
                                                                <div class="flex items-center gap-0.5 border-r border-zinc-200 dark:border-zinc-700 pr-1.5 mr-1.5">
                                                                    <flux:tooltip :content="__('Mark as primary key')">
                                                                        <button
                                                                            type="button"
                                                                            wire:click.stop="togglePrimaryKey({{ $column->id }})"
                                                                            class="p-0.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                                                            title="Primary Key"
                                                                        >
                                                                            <span class="text-[8px] font-bold px-1 py-0.5 rounded {{ $column->is_primary ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-300' : 'text-zinc-400 dark:text-zinc-500' }}">
                                                                                PK
                                                                            </span>
                                                                        </button>
                                                                    </flux:tooltip>
                                                                    <flux:tooltip :content="__('Mark as foreign key')">
                                                                    <button
                                                                        type="button"
                                                                        wire:click.stop="toggleForeignKey({{ $column->id }})"
                                                                        class="p-0.5 rounded hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors"
                                                                        title="Foreign Key"
                                                                    >
                                                                        <span class="text-[8px] font-bold px-1 py-0.5 rounded {{ $column->is_foreign ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'text-zinc-400 dark:text-zinc-500' }}">
                                                                            FK
                                                                        </span>
                                                                    </button>
                                                                    </flux:tooltip>
                                                                    <flux:tooltip :content="__('Mark as notnull')">
                                                                        <button
                                                                            type="button"
                                                                            wire:click.stop="toggleNullable({{ $column->id }})"
                                                                            class="p-0.5 rounded hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                                                        >
                                                                            <span class="text-[8px] font-bold px-1 py-0.5 rounded {{ $column->is_notnull ? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : 'text-zinc-400 dark:text-zinc-500' }}">
                                                                                N
                                                                            </span>
                                                                        </button>
                                                                    </flux:tooltip>
                                                                </div>
                                                                <button
                                                                    type="button"
                                                                    wire:click.stop="deleteColumnById({{ $column->id }})"
                                                                    wire:confirm="Tem certeza que deseja deletar esta coluna?"
                                                                    class="p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 transition-colors"
                                                                    title="Deletar coluna"
                                                                    wire:loading.attr="disabled"
                                                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                                                >
                                                                    <flux:icon.trash class="size-3 text-red-500 dark:text-red-400"/>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="bg-white dark:bg-zinc-800/50 rounded-lg min-h-[200px] flex justify-center items-center border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No columns found</p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <!-- Detalhes da Coluna Selecionada (Direita) -->
                                            <div class="h-full flex flex-col">
                                                @if($selectedColumnId)
                                                    @php
                                                        $selectedColumn = $this->getColumns()->firstWhere('id', $selectedColumnId);
                                                    @endphp
                                                    @if($selectedColumn)
                                                        <div class="bg-white dark:bg-zinc-800/50 rounded-md border border-zinc-200 dark:border-zinc-700 h-full flex flex-col">
                                                            <div class="px-4 py-3 justify-between border-b border-zinc-200/70 dark:border-zinc-700/70 flex-shrink-0">
                                                                <div class="flex items-center justify-between w-full">
                                                                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                                                        {!! Helpers::getIconDiamondByTypeColumn($selectedColumn) !!}
                                                                        <span>{{ $selectedColumn->name }}</span>
                                                                    </h3>

                                                                    <!-- Controles de Ordem -->
                                                                    <div class="flex items-center gap-2">
                                                                        <button
                                                                            type="button"
                                                                            wire:click="moveColumnUp({{ $selectedColumn->id }})"
                                                                            class="p-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                                                                            title="Mover para cima"
                                                                            @if($this->getColumns()->first()?->id === $selectedColumn->id) disabled @endif
                                                                        >
                                                                            <flux:icon.chevron-up class="cursor-pointer size-3 text-zinc-600 dark:text-zinc-400"/>
                                                                        </button>

                                                                        @php
                                                                            $position = $this->getColumns()->search(function($col) use ($selectedColumn) {
                                                                                return $col->id === $selectedColumn->id;
                                                                            }) + 1;
                                                                        @endphp

                                                                        <div class="flex flex-col items-center">
                                                                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-700 px-2.5 py-1 rounded-md min-w-[40px] text-center">
                                                                                {{ $position }} / {{ $this->getColumns()->count() }}
                                                                            </span>

                                                                        </div>

                                                                        <button
                                                                            type="button"
                                                                            wire:click="moveColumnDown({{ $selectedColumn->id }})"
                                                                            class="p-1 rounded hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                                                                            title="Mover para baixo"
                                                                            @if($this->getColumns()->last()?->id === $selectedColumn->id) disabled @endif
                                                                        >
                                                                            <flux:icon.chevron-down class="cursor-pointer size-3 text-zinc-600 dark:text-zinc-400"/>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="p-4 space-y-3 overflow-y-auto flex-1">
                                                                <!-- Formulário de Edição -->
                                                                <form
                                                                    x-trap="isEventUpdateColumn"
                                                                    x-data="{
                                                                        init() {
                                                                            $watch('isEventUpdateColumn', () => {
                                                                                $nextTick(() => {
                                                                                    $refs.firstInput?.focus();
                                                                                });
                                                                            });
                                                                        }
                                                                    }"
                                                                    wire:submit.prevent="updateColumn({{ $selectedColumn->id }})"
                                                                    class="space-y-3"
                                                                >
                                                                    <!-- Nome da Coluna -->
                                                                    <div>
                                                                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nome</label>
                                                                        <input
                                                                            x-ref="firstInput"
                                                                            type="text"
                                                                            wire:model="columnForm.name"
                                                                            class="w-full text-xs border border-zinc-300 dark:border-zinc-600 rounded-md px-2.5 py-1.5 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-[var(--table-base-bg)] focus:border-[var(--table-base-bg)]"
                                                                            placeholder="Nome da coluna"
                                                                        />
                                                                        @error('columnForm.name') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                                                    </div>

                                                                    <!-- Tipo e Length -->
                                                                    <div class="grid grid-cols-2 gap-2">
                                                                        <div>
                                                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tipo</label>
                                                                            <select
                                                                                wire:model="columnForm.type"
                                                                                class="w-full text-xs border border-zinc-300 dark:border-zinc-600 rounded-md px-2.5 py-1.5 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-[var(--table-base-bg)] focus:border-[var(--table-base-bg)]"
                                                                            >
                                                                                @foreach($this->getColumnTypes() as $type => $label)
                                                                                    <option value="{{ $type }}">{{ $label }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            @error('columnForm.type') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Length</label>
                                                                            <input
                                                                                type="number"
                                                                                wire:model="columnForm.length"
                                                                                min="0"
                                                                                class="w-full text-xs border border-zinc-300 dark:border-zinc-600 rounded-md px-2.5 py-1.5 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-[var(--table-base-bg)] focus:border-[var(--table-base-bg)]"
                                                                                placeholder="Ex: 255"
                                                                            />
                                                                            @error('columnForm.length') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                                                        </div>
                                                                    </div>

                                                                    <!-- Default Value -->
                                                                    <div>
                                                                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Valor Padrão</label>
                                                                        <input
                                                                            type="text"
                                                                            wire:model="columnForm.defaultValue"
                                                                            class="w-full text-xs border border-zinc-300 dark:border-zinc-600 rounded-md px-2.5 py-1.5 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-[var(--table-base-bg)] focus:border-[var(--table-base-bg)]"
                                                                            placeholder="Ex: 'default' ou NULL"
                                                                        />
                                                                        @error('columnForm.defaultValue') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                                                    </div>

                                                                    <!-- Comment -->
                                                                    <div>
                                                                        <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">Comentário</label>
                                                                        <textarea
                                                                            wire:model="columnForm.comment"
                                                                            rows="2"
                                                                            class="w-full text-xs border border-zinc-300 dark:border-zinc-600 rounded-md px-2.5 py-1.5 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-1 focus:ring-[var(--table-base-bg)] focus:border-[var(--table-base-bg)] resize-none"
                                                                            placeholder="Descrição da coluna..."
                                                                        ></textarea>
                                                                        @error('columnForm.comment') <span class="text-xs text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                                                                    </div>

                                                                    <!-- Botões de Ação -->
                                                                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-zinc-200/70 dark:border-zinc-700/70">
                                                                        <flux:button size="xs" wire:click="selectColumn(null)">{{ __('Cancel') }}</flux:button>
                                                                        <flux:button type="submit" size="xs" style="background-color: var(--table-base-bg); color: white;">{{ __('Save') }}</flux:button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="bg-white dark:bg-zinc-800/50 rounded-lg h-full flex justify-center items-center border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Selecione uma coluna para ver os detalhes</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        id="tab-foreign-keys"
                                        class="hidden"
                                        role="tabpanel"
                                        aria-labelledby="tabs-with-badges-item-2">
                                        <div class="grid grid-cols-[1fr_600px] gap-4 h-[45vh]">
                                            <!-- Lista de Foreign Keys (Esquerda) -->
                                            <div class="space-y-1 overflow-y-auto pr-2 h-full">
                                                @forelse($this->getForeignKeys() as $column)
                                                    <div class="bg-white dark:bg-zinc-800/50 rounded-md border border-zinc-200 dark:border-zinc-700 p-2 hover:border-zinc-300 dark:hover:border-zinc-600 hover:shadow-sm transition-all">
                                                        <div class="flex items-center gap-2">
                                                            {!! Helpers::getIconDiamondByTypeColumn($column) !!}
                                                            <span class="text-xs font-medium text-zinc-900 dark:text-zinc-100">{{ $column->name }}</span>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="bg-white dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 p-8 text-center">
                                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No foreign keys found</p>
                                                    </div>
                                                @endforelse
                                            </div>

                                            <!-- Grafo de Dependências (Direita) -->
                                            <div class="bg-white dark:bg-zinc-800/50 rounded-md border border-zinc-200 dark:border-zinc-700 h-full overflow-hidden">
                                                <div class="px-3 py-2 border-b border-zinc-200/70 dark:border-zinc-700/70">
                                                    <h3 class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Graph') }}</h3>
                                                </div>
                                                <div
                                                    id="foreign-keys-graph"
                                                    x-data="foreignKeysGraph(@js($this->getRelationships), @js($table->id), @js($table->name), @js($this->getTablesWithColors))"
                                                    @table-side-editor::column-added.window="
                                                        // Atualizar dados e re-renderizar gráfico
                                                        relationships = @js($this->getRelationships);
                                                        tablesColors = @js($this->getTablesWithColors);
                                                        svg = null;
                                                        setTimeout(() => {
                                                            if ($el && !svg) {
                                                                setupGraph();
                                                            }
                                                        }, 200);
                                                    "
                                                    class="h-[calc(45vh-50px)] w-full"
                                                ></div>
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
    @endif


</section>
