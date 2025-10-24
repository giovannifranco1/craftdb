<x-layouts.app-workspace :diagram="$diagram"  :title="__('Diagram')">
    @php
        $maxPerRow = 5;
        $spacingX = 500;
        $spacingY = 400;
    @endphp

    @foreach($tables as $key => $table)
        @php
            $key++;
            $row = floor(($key - 1) / $maxPerRow);
            $col = ($key - 1) % $maxPerRow;
            mt_srand($key * 2157 + 123);
            $dx = mt_rand(-22, 22); // desloca X levemente
            $dy = mt_rand(-20, 20); // desloca Y levemente
            $x = $col * $spacingX + $dx;
            $y = $row * $spacingY + $dy;

            $table->x = $x;
            $table->y = $y;
        @endphp

        <livewire:table :$table :key="$key" />
    @endforeach

</x-layouts.app-workspace>
