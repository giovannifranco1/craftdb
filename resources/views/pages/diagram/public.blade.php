<x-layouts.canvas.public :diagram="$diagram">

    <x-slot:areas>
        <livewire:diagram.public.canvas.lists.area-list :$diagram/>
    </x-slot:areas>

    <x-slot:notes>
        <livewire:diagram.public.canvas.lists.note-list :$diagram/>
    </x-slot:notes>

    <livewire:diagram.public.canvas.lists.table-list :$diagram/>

</x-layouts.canvas.public>
