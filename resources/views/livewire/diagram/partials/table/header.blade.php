<div
    x-data="{ loading: false, pressed: false }"
    @mousedown="pressed = true"
    @mouseup="if (pressed) {  loading = true; pressed = false; $dispatch('table-side-editor.open', { table: @js($table) });  }"
    @mouseleave="pressed = false"
    @table-side-editor-opened.window="loading = false;"
    :style="`height: ${getGridSize()/2 + getGridSize()}px; cursor: ${loading ? 'wait' : ''};`"
    class="bg-blue-500 flex-none border-b border-gray-200 px-3 py-1 rounded-t-xl table-header-beauty"
>
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
            <x-ui.diagram.table.dropdown-menu :table="$table" />
            <span class="text-white font-normal text-[12pt] font-[Kalam]">{{ $table->name }}</span>
        </h3>

        <button @mousedown.stop="toggleExpanded($el.closest('[table-box]'))"
            class="p-1.5 text-white hover:text-gray-700 hover:bg-gray-100 rounded-md transition-colors">
            <flux:icon.play variant="solid" x-show="expanded" class="size-4 rotate-90" />
            <flux:icon.play variant="solid" x-show="!expanded" class="size-4" />
        </button>
    </div>
</div>
