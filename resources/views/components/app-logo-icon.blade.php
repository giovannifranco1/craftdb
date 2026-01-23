<div x-data="{ isDarkTheme: $flux.dark }">
    <img x-show="isDarkTheme" src="{{ Vite::asset('resources/images/logo_blop_v2.png') }}" alt="CraftDB Logo" {{ $attributes }}>
    <img x-show="!isDarkTheme" src="{{ Vite::asset('resources/images/logo_blop_v2.png') }}" alt="CraftDB Logo" {{ $attributes }}>
</div>
