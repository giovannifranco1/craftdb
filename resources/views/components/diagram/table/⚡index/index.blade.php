<div
    x-data="runTable(@js($tableData->x), @js($tableData->y), @js($tableId))"
    x-show="@js($tableId)"
    id="table-{{ $tableId }}"
    table-id="{{ $tableId }}"
    table-box
    :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${getSnappedWidth()}px; max-width: ${getSnappedWidth()}px; ${zIndex ? `z-index: ${zIndex};` : ''}`"
    class="absolute h-full select-none rounded-xl shadow-lg dark:shadow-zinc-900/50">

    <x-diagram.table.ui.dropdown-menu :table-data="$tableData"/>

    <!-- Table -->
    <div class="rounded-xl w-full h-full flex flex-col relative">

        <!-- Header -->
        @include('components.diagram._partials.table.header')

        <!-- Table Structure -->
        @include('components.diagram._partials.table.structure')

        <!-- Footer -->
        @include('components.diagram._partials.table.footer')

    </div>
</div>
