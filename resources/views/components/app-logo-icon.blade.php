<div x-data="{ isDarkTheme: $flux.dark }">
    <img x-show="isDarkTheme" src="{{ Vite::asset('resources/images/final_flat_logo.svg') }}" alt="CraftDB Logo" {{ $attributes }}>
    <img x-show="!isDarkTheme" src="{{ Vite::asset('resources/images/final_flat_logo.svg') }}" alt="CraftDB Logo" {{ $attributes }}>
</div>
