<div style="position: static; inset: auto; transform: none; contain: layout;">
    @foreach($this->tables as $table)
        <div wire:key="table-css-{{ $table->id }}">
            <x-diagram.canvas.ui.table.css-table :table="$table" />
        </div>

        <livewire:diagram.canvas.elements.table :$table :wire:key="$table->id"/>
    @endforeach
</div>
