<x-layouts.canvas :diagram="$diagram">
    <x-slot:areas>
        <livewire:diagram.area-list :$diagram lazy="on-load"/>
    </x-slot:areas>

    <x-slot:notes>
        <livewire:diagram.note-list :$diagram lazy="on-load"/>
    </x-slot:notes>

    <livewire:diagram.table-list :$diagram lazy="on-load"/>

</x-layouts.canvas>
