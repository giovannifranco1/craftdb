@use('App\Support\Helpers')

<x-table.css-table :table="$table" />

<div id="table-{{ $table->id }}" table-box x-data="runTable(@js($table->x), @js($table->y))"
    :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${getSnappedWidth()}px; max-width: ${getSnappedWidth()}px`"
    class="absolute h-full select-none z-50 rounded-xl border border-gray-200 shadow-lg">
    <!-- Table -->
    <div class="rounded-xl w-full h-full flex flex-col relative">
        <!-- Header -->
        <div x-data="{ loading: false, pressed: false }"
            @mousedown="pressed = true"
            @mouseup="if (pressed) {  loading = true; pressed = false; $dispatch('table-side-editor.open', { table: @js($table) });  }"
            @mouseleave="pressed = false"
            @table-side-editor-opened.window="loading = false"
            :style="`height: ${getGridSize()/2 + getGridSize()}px; cursor: ${loading ? 'wait' : ''};`"
            class="bg-blue-500 flex-none border-b border-gray-200 px-3 py-1 rounded-t-xl table-header-beauty">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <div class="w-4 h-4 rounded text-white text-xs flex items-center justify-center font-bold table-icon">
                        <flux:icon.sheet class="size-3"/>
                    </div>
                    <span class="text-white font-normal">{{ $table->name }}</span>
                </h3>

                <div>
                    <button @mousedown.stop="toggleExpanded()"
                        class="p-1.5 text-white hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
                        <flux:icon.play variant="solid" x-show="expanded" class="size-4 rotate-90" />
                        <flux:icon.play variant="solid" x-show="!expanded" class="size-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Structure -->
        <div x-show="expanded" x-transition class="flex-auto bg-white relative">
            <table class="w-full table-fixed">
                <colgroup>
                    <col :style="`width: ${colWidths[0]}px`">
                    <col :style="`width: ${colWidths[1]}px`">
                </colgroup>

                <tbody class="divide-y divide-gray-100">

                    @foreach($this->getColumns() as $column)
                        <tr id="column-{{$table->id }}-{{ $column->id }}" class="hover:bg-blue-300/20 transition-colors" :style="`height: ${getGridSize()}px;`">
                            <td class="py-[2px] px-3">
                                <div class="flex items-center gap-2">
                                    {!! Helpers::getIconDiamondByTypeColumn($column) !!}
                                    <span class="text-xs font-medium text-gray-900">{{ $column->name }}</span>
                                </div>
                                <!-- inline column resize handle at end of first td -->
                                <span class="absolute top-0 right-0 h-full w-1 cursor-col-resize z-10" @mousedown.stop="startColResize(0, $event)" @touchstart.stop="startColResize(0, $event)"></span>
                            </td>

                            <td>
                                <span class="inline-flex items-center px-2 rounded text-xs uppercase font-medium bg-gray-100 text-gray-800">{{ $column->length > 0 ? "$column->type($column->length)" : $column->type }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 flex-none px-3 py-px rounded-b-xl table-footer-beauty">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex gap-3">
                    <span>{{ $table->columns_count }} campos</span>
                    <span>•</span>
                    <span>{{ $table->primary_count }} PK</span>
                    <span>•</span>
                    <span>{{ $table->foreign_count }} FK</span>
                </div>
                <button type="button" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors">
                    <flux:icon.arrow-down-tray class="size-4" />
                </button>
            </div>
        </div>
    </div>
</div>
