@props([
    'selected' => false,
    'color'    => null,
])

<div
    @class([
        'bg-[var(--table-base-bg)]',
        'size-4.5 rounded-full',
        $attributes?->get('class'),
        'border-2' => $selected,
    ])
    @if ($color) style="background-color: {{ $color }}" @endif
    title="table color: {{ $color ?? \App\Models\Table::COLOR_DEFAULT }}"
    {{ $attributes }}
></div>
