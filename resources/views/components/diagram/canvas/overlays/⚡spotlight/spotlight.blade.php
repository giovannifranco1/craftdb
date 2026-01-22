<flux:modal variant="bare" :closable="false" name="spotlight" class="min-w-2xl min-h-2/3">

    <div
        class="h-2/3 min-h-2/3"
        x-data
        @wheel.stop
    >
        <div class="bg-white p-2 rounded-2xl">
            <flux:input autocomplete="off" x-trap="true" wire:model.live="search" kbd="⌘K" icon="magnifying-glass" placeholder="Search..."/>
        </div>

        <div class="mt-3">
            @if($this->results->isNotEmpty())
                <ul
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto"
                    @wheel.stop
                >
                    @foreach ($this->results as $item)
                        <li>
                            <button
                                type="button"
                                class="w-full px-4 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150 group text-left"
                                wire:click="selectTable({{ $item->id }})"
                            >
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center border border-gray-200 dark:border-gray-600 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 transition-colors">
                                    <flux:icon.table class="size-5 text-gray-600 dark:text-gray-300" />
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-900 dark:text-gray-100 transition-colors">
                                            {{ $item->name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                            <flux:icon.layout-grid class="size-3" />
                                            {{ $item->columns_count ?? 0 }} {{ ($item->columns_count ?? 0) === 1 ? 'coluna' : 'colunas' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="size-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @elseif(!empty($this->search))
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <flux:icon.magnifying-glass class="size-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Nenhuma tabela encontrada
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Tente buscar por outro termo
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            <flux:icon.magnifying-glass class="size-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                Busque por tabelas
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Digite o nome de uma tabela para começar
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

</flux:modal>
