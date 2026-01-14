<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'CraftDB - A forma mais visual de projetar seu banco de dados' }}</title>
    <meta name="description" content="Crie diagramas de banco de dados intuitivos. Defina tabelas, relacionamentos e colabore em tempo real. Zero complexidade, máxima produtividade.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/landing-page.css'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white font-['Outfit',sans-serif] antialiased" x-data="{ mobileMenuOpen: false }">
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

            document.querySelectorAll('.scroll-animate').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

    @fluxScripts
</body>
</html>
