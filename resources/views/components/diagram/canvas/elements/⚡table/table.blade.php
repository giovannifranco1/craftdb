<div
    x-data="runTable(@js($tableData->x), @js($tableData->y), @js($tableId))"
    x-show="@js($tableId)"
    id="table-{{ $tableId }}"
    table-id="{{ $tableId }}"
    table-box
    wire:ignore.self
    :style="`
        transform: translate3d(${x}px, ${y}px, 0);
        width: ${getSnappedWidth()}px;
        transition: transform 0.03s ease-in-out;
        height: ${height}px;
        ${zIndex ? `z-index: ${zIndex};` : ''}
    `"
    class="absolute select-none rounded-xl dark:shadow-zinc-900/50">

    <x-diagram.canvas.ui.table.dropdown-menu :table-data="$tableData"/>

    <!-- Table -->
    <div class="rounded-xl w-full h-full flex flex-col relative" @table::removed.window="if ($root.getAttribute('table-id') == $event.detail.tableId) { $el.classList.add('opacity-50') }">
        <!-- Header -->
        @include('components.diagram.canvas._partials.table.header')

        <!-- Table Structure -->
        @include('components.diagram.canvas._partials.table.structure')

        <!-- Footer -->
        @include('components.diagram.canvas._partials.table.footer', ['tableColumnsLimit' => $this->tableColumnsLimit])

        <!-- Bottom Resize Handle -->
        <div
            class="absolute bottom-0 left-0 right-0 h-2 cursor-ns-resize z-10 resize-handle"
            data-resize="bottom"
            @mousedown.stop="startResize('bottom', $event)"
        ></div>
    </div>
</div>
