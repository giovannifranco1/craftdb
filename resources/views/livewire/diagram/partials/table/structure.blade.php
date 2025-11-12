@use('App\Support\Helpers')

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
                            <span class="text-xs font-medium text-gray-900 font-[Comic_Relief]">{{ $column->name }}</span>
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
