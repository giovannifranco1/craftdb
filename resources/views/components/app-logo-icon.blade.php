<div x-data="{ isDarkTheme: $flux.dark }">
    <img x-show="isDarkTheme" src="{{ Vite::asset('resources/images/logo.png') }}" alt="CraftDB Logo" {{ $attributes }}>
    <img x-show="!isDarkTheme" src="{{ Vite::asset('resources/images/logo.png') }}" alt="CraftDB Logo" {{ $attributes }}>
</div>
