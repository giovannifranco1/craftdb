<x-layouts.canvas :diagram="$diagram">

    <x-slot:areas>
        <livewire:diagram.area-list :$diagram/>
    </x-slot:areas>

    <x-slot:notes>
        <livewire:diagram.note-list :$diagram/>
    </x-slot:notes>

    <livewire:diagram.table-list :$diagram/>

</x-layouts.canvas>
