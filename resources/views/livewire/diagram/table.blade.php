<div
    x-data="runTable(@js($table->x), @js($table->y), @js($table->id))"
    x-show="@js($table->id)"
    id="table-{{ $table->id }}"
    table-id="{{ $table->id }}"
    table-box
    :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${getSnappedWidth()}px; max-width: ${getSnappedWidth()}px; ${zIndex ? `z-index: ${zIndex};` : ''}`"
    class="absolute h-full select-none rounded-xl shadow-lg dark:shadow-zinc-900/50">

    <x-ui.diagram.table.dropdown-menu :table="$table"/>

    <!-- Table -->
    <div @teste.window="console.log('teste', $event)" class="rounded-xl w-full h-full flex flex-col relative">

        <!-- Header -->
        @include('livewire.diagram.partials.table.header')

        <!-- Table Structure -->
        @include('livewire.diagram.partials.table.structure')

        <!-- Footer -->
        @include('livewire.diagram.partials.table.footer')

    </div>
</div>
