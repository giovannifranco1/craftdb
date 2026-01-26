<div
    x-data="runTable(@js($tableData->x), @js($tableData->y), @js($tableId))"
    x-show="@js($tableId)"
    id="table-{{ $tableId }}"
    table-id="{{ $tableId }}"
    table-box
    :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${getSnappedWidth()}px; max-width: ${getSnappedWidth()}px; ${zIndex ? `z-index: ${zIndex};` : ''}`"
    class="absolute h-full select-none rounded-xl shadow-lg dark:shadow-zinc-900/50">

    <!-- Table -->
    <div class="rounded-xl w-full h-full flex flex-col relative" @table::removed.window="if ($root.getAttribute('table-id') == $event.detail.tableId) { $el.classList.add('opacity-50') }">

        <!-- Header -->
        @include('components.diagram.public.canvas._partials.table.header')

        <!-- Table Structure -->
        @include('components.diagram.public.canvas._partials.table.structure')

        <!-- Footer -->
        @include('components.diagram.public.canvas._partials.table.footer')

    </div>
</div>
