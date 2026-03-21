<footer class="relative border-t border-gray-200/30">
    <div class="container mx-auto px-6 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <a href="{{ route('landing.index') }}" class="flex items-center gap-2 shrink-0">
                <x-app-logo-icon class="size-6 fill-current" />
                <span class="text-lg font-semibold text-gray-900">CraftDB</span>
            </a>

            <nav class="flex flex-wrap items-center justify-center gap-6">
                <a href="{{ route('landing.index') }}#features" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('landing-page.footer.features') }}</a>
                <a href="{{ route('landing.index') }}#pricing" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('landing-page.footer.pricing') }}</a>
                {{-- <a href="{{ route('landing.about') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Sobre</a> --}}
                <a href="{{ route('landing.terms') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('landing-page.footer.terms') }}</a>
                <a href="{{ route('landing.policy') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">{{ __('landing-page.footer.privacy') }}</a>
            </nav>

            <p class="text-sm text-gray-500 shrink-0">
                © {{ date('Y') }} CraftDB
            </p>
        </div>
    </div>
</footer>
