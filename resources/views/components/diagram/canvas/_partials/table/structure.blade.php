@use('App\Support\Helpers')

@if($hasColumnsInDatabase && !$loadTable)
    <div
        x-show="expanded"
        x-transition
        wire:intersect:enter.half.margin.200px="loadColumns"
        wire:intersect:leave.margin.500px="unloadColumns"
        class="flex-auto bg-white dark:bg-zinc-800 relative">
        @include('components.diagram.canvas._partials.table.structure-placeholder')
    </div>
@else
    <div
        x-show="expanded"
        x-transition
        class="flex-auto bg-white dark:bg-background-dark relative overflow-hidden">
        <table class="w-full table-fixed" wire:ignore.self>
            <colgroup>
                <col :style="`width: ${colWidths[0]}%`">
                <col :style="`width: ${colWidths[1]}%`">
            </colgroup>

            <tbody class="divide-y divide-gray-100 dark:divide-zinc-700">

                @foreach($tableData->columns as $index => $column)
                    <tr
                        x-init="if ($wire.tableData.resize_column_position < {{ $index + 1 }}) { $el.setAttribute('is-hidden', true); }"
                        x-data="{ isEventRenameColumn: false, loadingRenameColumn: false, renameActionTriggered: false, isHovering: false, hoverLeaveTimer: null }"
                        @mouseenter="clearTimeout(hoverLeaveTimer); isHovering = true"
                        @mouseleave="hoverLeaveTimer = setTimeout(() => { isHovering = false }, 150)"
                        id="column-{{ $tableId }}-{{ $column->id }}"
                        has-relationship-from="{{ $column->relationships_from_exists ? 'true' : 'false' }}"
                        has-relationship-to="{{ $column->relationships_to_exists ? 'true' : 'false' }}"
                        class="hover:bg-blue-300/20 dark:hover:bg-blue-500/20 transition-colors"
                        :style="`height: ${getGridSize()}px;`"
                        x-show="expanded && (hiddenColumnsCount === 0 || {{ $index }} < visibleColumnsCount)"
                        x-bind:is-hidden="!expanded || (hiddenColumnsCount > 0 && {{ $index }} >= visibleColumnsCount)"
                    >
                        <td @dblclick="isEventRenameColumn = true" class="px-3">
                            <flux:tooltip position="right" gap="100">
                                <div class="flex items-center gap-2">
                                    {!! Helpers::getIconDiamondByTypeColumn($column) !!}

                                    <div x-show="loadingRenameColumn" class="flex items-center">
                                        <div
                                            class="animate-spin mx-2 size-2.5 border-2 border-current border-t-transparent rounded-full dark:text-dark-500" role="status" aria-label="loading">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>

                                    <input
                                        x-data="{
                                            tableId: @js($tableId),
                                            columnId: @js($column->id),
                                            __new: @js($column->__new),

                                            init() {
                                                if (this.__new) {
                                                    this.isEventRenameColumn = true;
                                                }

                                                window.addEventListener(`table::column-added.${this.tableId}`, (event) => {
                                                    this.__new = false;
                                                    this.isEventRenameColumn = false;
                                                });
                                            },
                                        }"
                                        name="name"
                                        x-show="isEventRenameColumn"
                                        x-ref="inputColumn"
                                        x-trap="isEventRenameColumn"
                                        type="text"
                                        class="bg-transparent border-none outline-none shadow-none font-normal text-[14px] font-[Comic_Relief] p-0 m-0 w-auto"
                                        style="background: transparent !important; box-shadow: none !important;"
                                        @mousedown.stop
                                        @keydown.stop
                                        @keydown.enter="renameActionTriggered = true; loadingRenameColumn = true; $wire.renameColumn({{ $column->id }}, $refs.inputColumn?.value || '')"
                                        @keydown.escape.stop="isEventRenameColumn = false;"
                                        @table::column-renamed.window="() => {
                                            if (renameActionTriggered) isEventRenameColumn = false;
                                            loadingRenameColumn = false;
                                        }"
                                        @blur="() => {
                                            if (renameActionTriggered) return;

                                            if ($refs.inputColumn?.value === '') {
                                                $refs.inputColumn.value = 'new_column';
                                                isEventRenameColumn = false;
                                                renameActionTriggered = false;
                                                loadingRenameColumn = false;
                                                return;
                                            }

                                            renameActionTriggered = false;
                                            loadingRenameColumn = true;
                                            $wire.renameColumn({{ $column->id }}, $refs.inputColumn?.value);
                                        }"
                                    />
                                    <span
                                        x-show="!isEventRenameColumn"
                                        class="text-[14px] font-medium text-gray-900 dark:text-zinc-200 font-[Comic_Relief] max-w-[120px] truncate block"
                                        title="{{ $column->name }}"
                                    >
                                        {{ $column->name }}
                                    </span>
                                </div>

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

                        <td x-show="!isHovering">
                            <span class="inline-flex items-center px-2 w-20 justify-center rounded text-[11px] uppercase font-medium bg-gray-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200">
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

                        <td class="text-center align-middle pr-2" x-show="isHovering">
                            <button
                                type="button"
                                @click.stop="$wire.deleteColumn({{ $column->id }})"
                                wire:loading.attr="disabled"
                                wire:target="deleteColumn({{ $column->id }})"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center cursor-pointer justify-center w-20 h-4 shrink-0 rounded text-white bg-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-300 transition-colors"
                                title="{{ __('Remove column') }}"
                                aria-label="{{ __('Remove column') }}"
                            >
                                <flux:icon.minus class="size-2.5 shrink-0" />
                            </button>
                        </td>
                    </tr>
                @endforeach

                <!-- More columns indicator -->
                <tr
                    x-show="hiddenColumnsCount > 0"
                    x-transition
                    class="bg-gray-50 my-0 py-0 dark:bg-zinc-800/50 border-t-2 border-dashed border-gray-300 dark:border-zinc-600"
                    :style="`height: ${getGridSize() + 1.32}px`"
                >
                    <td colspan="3" class="px-3 text-center">
                        <span class="text-xs font-medium text-gray-600 dark:text-zinc-400 italic">
                            <span x-text="hiddenColumnsCount"></span> more ...
                        </span>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
@endif
