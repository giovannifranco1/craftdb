<div
    id="table-{{ $table->id }}" table-id="{{ $table->id }}" table-box x-data="runTable(@js($table->x), @js($table->y))"
    x-show="@js($table->id)"
    :style="`transform: translate3d(${x}px, ${y}px, 0); width: ${getSnappedWidth()}px; max-width: ${getSnappedWidth()}px`"
    class="absolute h-full select-none z-50 rounded-xl shadow-lg">
    <!-- Table -->
    <div class="rounded-xl w-full h-full flex flex-col relative">

        <!-- Header -->
        @include('livewire.diagram.partials.table.header')

        <!-- Table Structure -->
        @include('livewire.diagram.partials.table.structure')

        <!-- Footer -->
        @include('livewire.diagram.partials.table.footer')

    </div>
</div>
