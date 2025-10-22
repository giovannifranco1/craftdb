<x-layouts.app-drag-workspace :title="__('Diagram')">
    <div class="absolute top-0 left-0 origin-top-left transition-transform duration-100 ease-out"
        :style="`transform: translate(${offsetX}px, ${offsetY}px) scale(${scale});`">

        @php
            $maxPerRow = 5;
            $spacingX = 500;
            $spacingY = 400;
            $tables = $diagram->tables()->get();
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

                // $table->x = $x;
                // $table->y = $y;
            @endphp

            <livewire:table :$table :key="$key" />
        @endforeach
    </div>
</x-layouts.app-drag-workspace>
