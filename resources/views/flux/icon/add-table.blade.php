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
    width="24" height="23" viewBox="0 0 24 23" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M3.64155 2.02911H17.7204C18.5018 2.37717 19.1276 2.99467 19.4803 3.76571V12.4487M12.4408 19.3951H3.64155C2.86714 19.1982 2.18376 18.747 1.70394 18.1157C1.22413 17.4844 0.976471 16.7106 1.00176 15.9219V3.76571C1.34644 3.16324 1.86561 2.67608 2.4927 2.36668C3.1198 2.05728 3.82624 1.93973 4.52148 2.02911M19.4803 15.0536V22M15.9606 18.5268H23M1.00176 7.23891H19.4803" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
</svg>

