<x-layouts.canvas :diagram="$diagram" :title="__('Diagram')">

    <livewire:diagram.table-list :$diagram lazy="on-load"/>

</x-layouts.canvas>
