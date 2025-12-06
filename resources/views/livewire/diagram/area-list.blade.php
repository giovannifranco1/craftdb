<div style="position: static; inset: auto; transform: none; contain: layout;">
    @foreach($this->getAreas() as $area)
        <livewire:diagram.area :$area :key="$area->id" />
    @endforeach
</div>
