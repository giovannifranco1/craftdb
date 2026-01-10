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
    {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round">
    <path d="M9.16666 18.3333H8.93941C6.22174 18.3333 4.86291 18.3333 3.91924 17.6685C3.64882 17.478 3.40882 17.2521 3.20641 16.9976C2.49999 16.1095 2.49999 14.8305 2.49999 12.2727V10.1515C2.49999 7.68218 2.49999 6.44759 2.89074 5.46151C3.51899 3.87618 4.84757 2.62576 6.53199 2.03451C7.57966 1.66668 8.89149 1.66668 11.5152 1.66668C13.0144 1.66668 13.764 1.66668 14.3627 1.87684C15.3252 2.21468 16.0844 2.92926 16.4434 3.83509C16.6667 4.39859 16.6667 5.10409 16.6667 6.51518V10" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M7.66666 1.66667C9.20074 1.66667 10.4444 2.91033 10.4444 4.44442C10.4444 4.99925 10.3472 5.65333 10.4917 6.19283C10.6202 6.67208 10.9946 7.0465 11.4738 7.17492C12.0133 7.31942 12.6674 7.22225 13.2222 7.22225C14.7563 7.22225 16 8.46592 16 10" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M10.3333 15.6667H17M13.6667 19V12.3333" stroke="currentColor" stroke-width="1.66667" stroke-linecap="round"/>
</svg>
