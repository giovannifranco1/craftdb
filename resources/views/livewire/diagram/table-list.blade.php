@use('App\Support\Helpers')

@php

    $offsets = Helpers::getPositionCanvasByDiagram($this->diagram);

@endphp

<div x-init="$dispatch('tables-list::inicializeted', @js($offsets))" style="position: static; inset: auto; transform: none; contain: layout;">
    @php
        $maxPerRow = 5;
        $spacingX = 500;
        $spacingY = 400;
    @endphp

    @foreach($this->getTables() as $key => $table)
        @php
            $key++;
            $row = floor(($key - 1) / $maxPerRow);
            $col = ($key - 1) % $maxPerRow;
            mt_srand($key * 2157 + 123);
            $dx = mt_rand(-22, 22); // desloca X levemente
            $dy = mt_rand(-20, 20); // desloca Y levemente
            $x = $col * $spacingX + $dx;
            $y = $row * $spacingY + $dy;

            if (is_null($table?->id)) {
                $table->x = $x;
                $table->y = $y;
            }
        @endphp

        <div wire:key="table-css-{{ $table->id }}">
            <x-table.css-table :table="$table" />
        </div>

        <livewire:diagram.table :$table :key="$table->id" />
    @endforeach
</div>
