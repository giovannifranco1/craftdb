<x-layouts.canvas :diagram="$diagram" :title="__('Diagram')">

    <x-slot:areas>
        <livewire:diagram.area-list :$diagram lazy="on-load"/>
    </x-slot:areas>

    <livewire:diagram.table-list :$diagram lazy="on-load"/>

</x-layouts.canvas>
