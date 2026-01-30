<div style="position: static; inset: auto; transform: none; contain: layout;">
    @foreach($this->notes as $note)
        <livewire:diagram.canvas.elements.note :$note :wire:key="$note->id" />
    @endforeach
</div>
