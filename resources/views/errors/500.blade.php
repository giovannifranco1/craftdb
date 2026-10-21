<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>500 - Erro no servidor | CraftDB</title>
    <link rel="icon" href="/favicon.ico" sizes="192x192">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7fcff] font-['Outfit',sans-serif] antialiased flex flex-col items-center justify-center px-6">
    <div class="text-center max-w-md">
        <h1 class="text-8xl font-bold text-zinc-800 mb-4">500</h1>
        <h2 class="text-2xl font-semibold text-zinc-700 mb-4">{{ __('Server Error') }}</h2>
        <p class="text-zinc-600 mb-8">
            {{ __('Something went wrong on our end. Please try again later.') }}
        </p>
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white bg-gray-900 rounded-full hover:bg-gray-800 transition-colors">
            {{ __('Back to home') }}
        </a>
    </div>
</body>
</html>
