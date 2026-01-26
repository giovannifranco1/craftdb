<div style="position: static; inset: auto; transform: none; contain: layout;">
    @foreach($this->getAreas() as $area)
        <livewire:diagram.public.canvas.elements.area :$area :wire:key="$area->id" />
    @endforeach
</div>
