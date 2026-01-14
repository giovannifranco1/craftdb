<div style="position: static; inset: auto; transform: none; contain: layout;">
    @foreach($this->getNotes() as $note)
        <livewire:diagram.note :$note :key="$note->id" />
    @endforeach
</div>
