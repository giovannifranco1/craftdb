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
    data-flux-icon aria-hidden="true" width="24" height="22" viewBox="0 0 24 22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M19 13V21M15 17H23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    <path d="M12 19H2C1.44771 19 1 18.5523 1 18V2C1 1.44772 1.44772 1 2 1H18C18.5523 1 19 1.44772 19 2V10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4"/>
</svg>



