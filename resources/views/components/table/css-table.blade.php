<style>

#table-{{$table->id}} {
    --table-footer-bg: {{ $color }};
    --table-header-bg: {{ $color }};
    --table-icon-bg: color-mix(in srgb, var(--table-header-bg), white 20%);
}

#table-{{$table->id}} .table-footer-beauty {
    border-bottom-width: 5px;
    border-bottom-style: solid;
    border-bottom-color: var(--table-footer-bg);
    border-image: none;
    box-shadow: none;
}

#table-{{$table->id}} .table-header-beauty {
    background-color: var(--table-header-bg);
}

#table-{{$table->id}} .table-header-beauty .table-icon {
    background-color: var(--table-icon-bg);
}


</style>
