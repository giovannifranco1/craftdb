<footer class="relative border-t border-gray-200/30">
    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <x-app-logo-icon class="size-6 fill-current" />
                <span class="text-lg font-semibold text-gray-900">CraftDB</span>
            </a>

            <nav class="flex flex-wrap items-center justify-center gap-6">
                <a href="{{ route('landing.features') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Recursos</a>
                <a href="{{ route('landing.pricing') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Preços</a>
                <a href="{{ route('landing.about') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Sobre</a>
                <a href="{{ route('landing.terms') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Termos</a>
                <a href="{{ route('landing.policy') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Privacidade</a>
            </nav>

            <p class="text-sm text-gray-500 shrink-0">
                © {{ date('Y') }} CraftDB
            </p>
        </div>
    </div>
</footer>
