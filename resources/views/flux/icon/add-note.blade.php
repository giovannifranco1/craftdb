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
    width="21" height="22" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g clip-path="url(#clip0_2028_103)">
    <path d="M10.5 18.3333L8.93941 18.3333C6.22174 18.3333 4.86291 18.3333 3.91924 17.6685C3.64882 17.478 3.40882 17.2521 3.20641 16.9976C2.49999 16.1094 2.49999 14.8305 2.49999 12.2727V10.1515C2.49999 7.68217 2.49999 6.44759 2.89074 5.4615C3.51899 3.87617 4.84757 2.62575 6.53199 2.0345C7.57966 1.66667 8.89149 1.66667 11.5152 1.66667C13.0144 1.66667 13.764 1.66667 14.3627 1.87683C15.3252 2.21467 16.0844 2.92925 16.4434 3.83509C16.6667 4.39859 16.6667 5.10409 16.6667 6.51517V11.5" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M7 2C8.65681 2 9.99997 3.34316 9.99997 4.99997C9.99997 5.59919 9.89503 6.3056 10.0511 6.88826C10.1898 7.40585 10.5941 7.81022 11.1117 7.94891C11.6944 8.10497 12.4008 8.00003 13 8.00003C14.6568 8.00003 16.5 9.84319 16.5 11.5" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M16.5197 14V20.9464M13 17.4732H20.0394" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </g>
    <defs>
    <clipPath id="clip0_2028_103">
    <rect width="21" height="22" fill="none" transform="matrix(-1 0 0 -1 21 22)"/>
    </clipPath>
    </defs>
</svg>
