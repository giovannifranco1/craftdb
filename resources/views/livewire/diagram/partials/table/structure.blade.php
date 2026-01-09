@use('App\Support\Helpers')

<div
    x-show="expanded"
    x-transition
    class="flex-auto bg-white dark:bg-zinc-800 relative">
    <table class="w-full table-fixed">
        <colgroup>
            <col :style="`width: ${colWidths[0]}px`">
            <col :style="`width: ${colWidths[1]}px`">
            <col style="width: 10%">
        </colgroup>

        <tbody  class="divide-y divide-gray-100 dark:divide-zinc-700">

            @foreach($this->getColumns() as $column)
                <tr x-data="{ isEventRenameColumn: false, loadingRenameColumn: false }" id="column-{{$table->id }}-{{ $column->id }}" class="hover:bg-blue-300/20 dark:hover:bg-blue-500/20 transition-colors" :style="`height: ${getGridSize()}px;`">
                    <td @dblclick="isEventRenameColumn = true" class="py-[2px] px-3">
                        <div class="flex items-center gap-2">
                            {!! Helpers::getIconDiamondByTypeColumn($column) !!}

                            <div x-show="loadingRenameColumn" class="flex items-center">
                                <div
                                    class="animate-spin mx-2 size-2.5 border-2 border-current border-t-transparent rounded-full dark:text-dark-500" role="status" aria-label="loading">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>

                            <input
                                name="name"
                                x-show="isEventRenameColumn"
                                x-ref="inputColumn"
                                x-trap="isEventRenameColumn"
                                type="text"
                                class="bg-transparent border-none outline-none shadow-none font-normal text-xs font-[Comic_Relief] p-0 m-0 w-auto"
                                style="background: transparent !important; box-shadow: none !important;"
                                @mousedown.stop
                                @keydown.stop
                                @keydown.enter="loadingRenameColumn = true; $wire.renameColumn({{ $column->id }}, $refs.inputColumn?.value || null)"
                                @keydown.escape.stop="isEventRenameColumn = false;"
                                @table::column-renamed.window="isEventRenameColumn = false; loadingRenameColumn = false;"
                                @blur="loadingRenameColumn = true; $wire.renameColumn({{ $column->id }}, $refs.inputColumn?.value || null)"
                            />
                            <span
                                x-show="!isEventRenameColumn"
                                class="text-xs font-medium text-gray-900 dark:text-zinc-200 font-[Comic_Relief] max-w-[120px] truncate block"
                                title="{{ $column->name }}"
                            >
                                {{ $column->name }}
                            </span>
                        </div>
                    </td>

                    <td>
                        <span class="inline-flex items-center px-2 rounded text-[11px] uppercase font-medium bg-gray-100 dark:bg-zinc-700 text-gray-800 dark:text-zinc-200">
                            @if($column->length > 0)
                                @php
                                    $lengthStr = (string)$column->length;
                                    $displayLength = strlen($lengthStr) > 3 ? substr($lengthStr, 0, 3) : $lengthStr;
                                @endphp
                                {{ "$column->type($displayLength)" }}
                            @else
                                {{ $column->type }}
                            @endif
                        </span>
                    </td>

                    <td>
                        <flux:tooltip toggleable position="right">
                            <button>
                                <flux:icon.information-circle class="size-3 text-primary-700 dark:text-primary-300" />
                            </button>
                            <flux:tooltip.content>
                                <div class="space-y-2 min-w-[200px]">
                                    <!-- Nome da Coluna -->
                                    <div>
                                        <h4 class="font-semibold text-sm text-white dark:text-zinc-100 mb-1">{{ $column->name }}</h4>
                                        <p class="text-xs text-zinc-400">
                                            @if($column->length > 0)
                                                @php
                                                    $lengthStr = (string)$column->length;
                                                    $displayLength = strlen($lengthStr) > 3 ? substr($lengthStr, 0, 3) : $lengthStr;
                                                @endphp
                                                {{ "$column->type($displayLength)" }}
                                            @else
                                                {{ $column->type }}
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Badges -->
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($column->is_primary)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-300">
                                                <flux:icon.key variant="solid" class="size-2.5" />
                                                PRIMARY
                                            </span>
                                        @endif
                                        @if($column->is_foreign)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                                <flux:icon.link variant="solid" class="size-2.5" />
                                                FOREIGN
                                            </span>
                                        @endif
                                        @if($column->is_nullable)
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                NULL
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                                                NOT NULL
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Valor Padrão -->
                                    @if($column->default_value)
                                        <div class="pt-1 border-t border-gray-200 dark:border-zinc-700">
                                            <p class="text-[10px] font-medium text-gray-500 dark:text-zinc-400 uppercase mb-0.5">Valor Padrão</p>
                                            <p class="text-xs text-zinc-300 font-mono rounded">
                                                {{ $column->default_value }}
                                            </p>
                                        </div>
                                    @endif

                                    <!-- Comentário -->
                                    @if($column->comment)
                                        <div class="pt-1 border-t border-gray-200 dark:border-zinc-700">
                                            <p class="text-[10px] font-medium text-gray-500 dark:text-zinc-400 uppercase mb-0.5">Comentário</p>
                                            <p class="text-xs text-zinc-300 leading-relaxed">
                                                {{ $column->comment }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </flux:tooltip.content>
                        </flux:tooltip>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
