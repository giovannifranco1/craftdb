<section class="relative min-h-screen flex items-center pt-32 overflow-hidden">
    <!-- Background image -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-40" style="background-image: url('{{ Vite::asset('resources/images/landing-page/hero_background_f7fcff_detailed.png') }}');"></div>

    <!-- Background decorations -->
    <div class="absolute inset-0 hero-gradient opacity-30"></div>
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-gray-900/5 rounded-full blur-3xl"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-gray-700/5 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left side - Text content -->
            <div class="text-left">
                <!-- Headline -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight fade-in-up">
                    {{ __('landing-page.hero.headline_prefix') }}
                    <span class="bg-gradient-to-r from-gray-800 via-gray-500 to-gray-900 bg-clip-text text-transparent">{{ __('landing-page.hero.headline_gradient') }}</span>
                </h1>

                <!-- Subheadline -->
                <p class="text-lg md:text-xl text-gray-600 mb-10 max-w-xl fade-in-up fade-in-delay-1">
                    {{ __('landing-page.hero.subheadline') }}
                </p>

                <!-- CTAs -->
                <div class="flex flex-col sm:flex-row items-start gap-4 mb-8 fade-in-up fade-in-delay-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary group">
                            {{ __('landing-page.hero.cta_dashboard') }}
                            <x-lucide-arrow-right class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary group">
                            {{ __('landing-page.hero.cta_register') }}
                            <x-lucide-arrow-right class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </a>
                    @endauth
                    <a href="#features" class="btn-secondary group">
                        {{ __('landing-page.hero.cta_secondary') }}
                    </a>
                </div>

                <!-- Support text -->
                <p class="text-sm text-gray-600 fade-in-up fade-in-delay-3">
                    {{ __('landing-page.hero.tech_line') }}
                </p>
            </div>

            <!-- Right side - MacBook Mockup -->
            <x-landing-page.macbook-mockup
                screen="resources/images/landing-page/image_macbook.png"
                :alt="__('landing-page.hero.macbook_screen_alt')"
                class="fade-in-up fade-in-delay-3"
            />
        </div>
    </div>
</section>
