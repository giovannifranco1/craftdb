@props([
    'title' => null,
    'description' => null,
])

@php
    $pageTitle = $title ?? __('landing-page.meta.default_title');
    $pageDescription = $description ?? __('landing-page.meta.default_description');
    $htmlLang = app()->getLocale() === 'pt_BR' ? 'pt-BR' : 'en';
    $ogLocale = app()->getLocale() === 'pt_BR' ? 'pt_BR' : 'en_US';
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">

    {{-- Open Graph (OG) --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ asset('preview.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:site_name" content="CraftDB">

    <link rel="icon" href="/favicon.ico?v=2" sizes="256x256">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">


    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/landing-page.css'])

    @fluxAppearance
</head>
<body class="min-h-screen bg-[#f7fcff] font-['Outfit',sans-serif] antialiased" x-data="{ mobileMenuOpen: false }">
    <!-- Header -->
    @include('pages::landing-page._partials.header')

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    @include('pages::landing-page._partials.footer')

    <!-- Scroll Animation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.scroll-animate, .zoom-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

    @fluxScripts
</body>
</html>
