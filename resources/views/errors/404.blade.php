<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 - Página não encontrada | CraftDB</title>
    <link rel="icon" href="/favicon.ico" sizes="192x192">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7fcff] font-['Outfit',sans-serif] antialiased flex flex-col items-center justify-center px-6">
    <div class="text-center max-w-md">
        <h1 class="text-8xl font-bold text-zinc-800 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-zinc-700 mb-4">{{ __('Page not found') }}</h2>
        <p class="text-zinc-600 mb-8">
            {{ __('The page you are looking for does not exist or has been moved.') }}
        </p>
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-white bg-gray-900 rounded-full hover:bg-gray-800 transition-colors">
            {{ __('Back to home') }}
        </a>
    </div>
</body>
</html>
