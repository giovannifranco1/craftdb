{{-- Credit: Lucide (https://lucide.dev) --}}

@props([
    'variant' => 'outline',
])

@php
    if ($variant === 'solid') {
        throw new \Exception('The "solid" variant is not supported in Lucide.');
    }

    $classes = Flux::classes('shrink-0')->add(
        match ($variant) {
            'outline' => '[:where(&)]:size-6',
            'solid' => '[:where(&)]:size-6',
            'mini' => '[:where(&)]:size-5',
            'micro' => '[:where(&)]:size-4',
        },
    );

    $strokeWidth = match ($variant) {
        'outline' => 2,
        'mini' => 2.25,
        'micro' => 2.5,
    };
@endphp
<svg
    {{ $attributes->class($classes) }}
    data-flux-icon
    aria-hidden="true"
    viewBox="0 0 26 26"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
>
    <path d="M4 2 L20 2 A4 4 0 0 1 22 4 L22 14 M14 22 L4 22 A4 4 0 0 1 1 18 L1 4 A4 4 0 0 1 5 2 M22 17 L22 25 M18 21 L26 21 M1 8 L22 8"></path>
</svg>
