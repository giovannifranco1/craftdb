<div style="position: static; inset: auto; transform: none; contain: layout;">
    @foreach($this->getTables as $table)
        <div wire:key="table-css-{{ $table->id }}">
            <x-table.css-table :table="$table" />
        </div>

    <livewire:diagram.table :$table :key="$table->id" />
    @endforeach
</div>
