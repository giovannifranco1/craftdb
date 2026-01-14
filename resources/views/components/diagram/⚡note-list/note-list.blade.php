<div style="position: static; inset: auto; transform: none; contain: layout;">
    @foreach($this->getNotes() as $note)
        <livewire:diagram.note :$note :wire:key="$note->id" />
    @endforeach
</div>
