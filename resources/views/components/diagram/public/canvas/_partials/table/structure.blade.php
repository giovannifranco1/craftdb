@use('App\Support\Helpers')

@if($hasColumnsInDatabase && !$loadTable)
    <div
        x-show="expanded"
        x-transition
        wire:intersect:enter.half.margin.1500px.once="loadColumns"
        class="flex-auto bg-white dark:bg-zinc-800 relative">
        @include('components.diagram.public.canvas._partials.table.structure-placeholder')
    </div>
@else
    <div
        x-show="expanded"
        x-transition
        class="flex-auto bg-white dark:bg-background-dark relative">
        <table class="w-full table-fixed" wire:ignore.self>
            <colgroup>
                <col :style="`width: ${colWidths[0]}px`">
                <col :style="`width: ${colWidths[1]}px`">
                <col style="width: 10%">
            </colgroup>

            <tbody class="divide-y divide-gray-100 dark:divide-zinc-700">

                @foreach($tableData->columns as $column)
                    <tr x-data="{ isEventRenameColumn: false, loadingRenameColumn: false, renameActionTriggered: false }" id="column-{{ $tableId }}-{{ $column->id }}" class="hover:bg-blue-300/20 dark:hover:bg-blue-500/20 transition-colors" :style="`height: ${getGridSize()}px;`">
                        <td class="py-[2px] px-3">
                            <div class="flex items-center gap-2">
                                {!! Helpers::getIconDiamondByTypeColumn($column) !!}

                                <span class="text-[13px] font-medium text-gray-900 dark:text-zinc-200 max-w-[120px] truncate block" title="{{ $column->name }}">
                                    {{ $column->name }}
                                </span>
                            </div>
                        </td>

                        <td>
                            <span class="inline-flex items-center px-2 rounded text-[10px] uppercase font-medium bg-gray-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
                                @if(($column->length ?? 0) > 0)
                                    @php
                                        $lengthStr = (string)$column->length;
                                        $displayLength = strlen($lengthStr) > 3 ? substr($lengthStr, 0, 3) : $lengthStr;
                                    @endphp
                                    {{ $column->type }}
                                @else
                                    {{ $column->type }}
                                @endif
                            </span>
                        </td>

                        <td>
                            <flux:tooltip toggleable gap="28" position="right">
                                <button>
                                    <flux:icon.information-circle class="size-3 text-primary-700 dark:text-primary-300" />
                                </button>
                                <flux:tooltip.content>
                                    <div class="space-y-2 min-w-[200px]">
                                        <!-- Nome da Coluna -->
                                        <div>
                                            <h4 class="font-semibold text-sm text-white dark:text-zinc-100 mb-1">{{ $column->name }}</h4>
                                            <p class="text-xs text-zinc-400">
                                                @if(($column->length ?? 0) > 0)
                                                    @php
                                                        $lengthStr = (string)$column->length;
                                                        $displayLength = strlen($lengthStr) > 3 ? substr($lengthStr, 0, 3) : $lengthStr;
                                                    @endphp
                                                    {{ $column->type }} ({{ $displayLength }})
                                                @else
                                                    {{ $column->type }}
                                                @endif
                                            </p>
                                        </div>

                                        <!-- Badges -->
                                        <div class="flex flex-wrap gap-1.5">
                                            @if($column->isPrimary())
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-300">
                                                    <flux:icon.key variant="solid" class="size-2.5" />
                                                    PRIMARY
                                                </span>
                                            @endif
                                            @if($column->isForeign())
                                                <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                                    <flux:icon.link variant="solid" class="size-2.5" />
                                                    FOREIGN
                                                </span>
                                            @endif
                                            @if($column->isNullable())
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
                                        @if(($column->default_value ?? null))
                                            <div class="pt-1 border-t border-gray-200 dark:border-zinc-700">
                                                <p class="text-[10px] font-medium text-gray-500 dark:text-zinc-400 uppercase mb-0.5">Valor Padrão</p>
                                                <p class="text-xs text-zinc-300 font-mono rounded">
                                                    {{ $column->default_value }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Comentário -->
                                        @if(($column->comment ?? null))
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
@endif
