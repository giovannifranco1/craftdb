<div x-data="{ isDarkTheme: $flux.dark }">
    <img x-show="isDarkTheme" src="{{ Vite::asset('resources/images/craftdb_logo_transparent_dark.png') }}" alt="CraftDB Logo" {{ $attributes }} class="w-10 h-10">
    <img x-show="!isDarkTheme" src="{{ Vite::asset('resources/images/craftdb_logo_transparent_light.png') }}" alt="CraftDB Logo" {{ $attributes }} class="w-10 h-10">
</div>
