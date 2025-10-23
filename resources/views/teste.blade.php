@use('App\Models\Table')
@use('App\Models\Diagram')

@php
    $diagram = Diagram::find(1);
@endphp

<x-layouts.app-drag-workspace-two :diagram="$diagram">

    @php
        $table = Table::find(1);
        $table->x = 24;
        $table->y = 24;

        $table2 = Table::find(2);
        $table2->x = 500;
        $table2->y = 64;

        $table3 = Table::find(3);
        $table3->x = 20;
        $table3->y = 400;

    @endphp

    <livewire:table :table="$table" />

    <livewire:table :table="$table2" />

    <livewire:table :table="$table3" />


</x-layouts.app-drag-workspace-two>
