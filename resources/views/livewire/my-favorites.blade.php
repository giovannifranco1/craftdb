<x-slot:title>My Favorites</x-slot:title>

<div>

    <livewire:diagram.table-side-editor/>
    <livewire:diagram.table-relationships-side-editor/>

    <flux:button @click.stop="$dispatch('table-side-editor.open')" variant="primary">Table Side</flux:button>
    <flux:button @click.stop="$dispatch('table-relationships-side-editor.open')" variant="primary">Table Side relationships</flux:button>

</div>
