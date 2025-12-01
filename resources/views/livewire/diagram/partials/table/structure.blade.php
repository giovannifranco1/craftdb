@use('App\Support\Helpers')

<div
    x-show="expanded"
    x-transition
    class="flex-auto bg-white dark:bg-zinc-800 relative">
    <table class="w-full table-fixed">
        <colgroup>
            <col :style="`width: ${colWidths[0]}px`">
            <col :style="`width: ${colWidths[1]}px`">
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
                                @blur="isEventRenameColumn = false; $wire.refreshTableWithCounts()"
                            />
                            <span x-show="!isEventRenameColumn" class="text-xs font-medium text-gray-900 dark:text-zinc-200 font-[Comic_Relief]">{{ $column->name }}</span>
                        </div>
                        <!-- inline column resize handle at end of first td -->
                        <span class="absolute top-0 right-0 h-full w-1 cursor-col-resize z-10" @mousedown.stop="startColResize(0, $event)" @touchstart.stop="startColResize(0, $event)"></span>
                    </td>

                    <td>
                        <span class="inline-flex items-center px-2 rounded text-xs uppercase font-medium bg-gray-100 dark:bg-zinc-700 text-gray-800 dark:text-zinc-200">{{ $column->length > 0 ? "$column->type($column->length)" : $column->type }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
