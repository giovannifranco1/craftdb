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
    width="24" height="22" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M19.5 14.0326V21M16 17.5163H23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    <path d="M12.0138 19.0225H2C1.44772 19.0225 1 18.5748 1 18.0225V2C1 1.44772 1.44772 1 2 1H18.0225C18.5748 1 19.0225 1.44772 19.0225 2V10.0113" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4"/>
    </svg>




