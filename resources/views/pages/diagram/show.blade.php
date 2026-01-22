<x-layouts.canvas :diagram="$diagram">

    <x-slot:areas>
        <livewire:diagram.canvas.lists.area-list :$diagram/>
    </x-slot:areas>

    <x-slot:notes>
        <livewire:diagram.canvas.lists.note-list :$diagram/>
    </x-slot:notes>

    <livewire:diagram.canvas.lists.table-list :$diagram/>

</x-layouts.canvas>
