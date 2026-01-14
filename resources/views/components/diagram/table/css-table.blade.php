<style>

[table-id="{{ $table->id }}"] {
    --table-base-bg: {{ $color }};
    --table-footer-bg: {{ $color }};
    --table-header-bg: {{ $color }};
    --table-header-icon-bg: color-mix(in srgb, var(--table-header-bg), white 20%);
}

</style>
