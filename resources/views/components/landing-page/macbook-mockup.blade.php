@props([
    'screen',
    'alt' => '',
])

{{-- Mockup de MacBook reutilizável: moldura (macbook.png, com área da tela transparente)
     sobreposta ao print da tela informado em :screen. Mesma proporção/posição do hero.
     Aceita classes extras no elemento raiz (ex.: fade-in-up) via merge de atributos. --}}
<div {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="relative">
        <img
            src="{{ Vite::asset('resources/images/landing-page/macbook.png') }}"
            alt="{{ __('landing-page.hero.macbook_alt') }}"
            class="w-full h-auto relative z-10"
        />
        <img
            src="{{ Vite::asset($screen) }}"
            alt="{{ $alt }}"
            class="absolute w-[70%] h-auto top-[14%] left-[15%] z-5"
        />
    </div>

    {{-- Sombra --}}
    <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 w-[80%] h-8 bg-black/10 blur-2xl rounded-full"></div>
</div>
