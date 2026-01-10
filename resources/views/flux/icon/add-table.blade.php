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
    data-flux-icon aria-hidden="true" width="26" height="26" viewBox="0 0 26 26" fill="none" stroke="currentColor" stroke-width="2">
    <g clip-path="url(#clip0_2028_107)">
    <path d="M3.99999 1.99998H20C20.888 2.40084 21.5991 3.112 22 3.99998V14M14 22H3.99999C3.11991 21.7732 2.34328 21.2535 1.79799 20.5265C1.25269 19.7994 0.971246 18.9084 0.999988 18V3.99998C1.3917 3.30614 1.98171 2.74509 2.69437 2.38876C3.40704 2.03242 4.20988 1.89705 4.99999 1.99998M22 17V25M18 21H26M0.999988 7.99998H22" stroke="currentColor" stroke-width="2"/>
    </g>
    <defs>
    <clipPath id="clip0_2028_107">
    <rect width="26" height="26" fill="white"/>
    </clipPath>
    </defs>
</svg>
