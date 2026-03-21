@php
    $p = __('landing-page.pricing.plans');
@endphp
<section id="pricing" class="py-24 relative section-gradient scroll-animate">
    <div class="container mx-auto px-6" x-data="{ annual: false }">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-10">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                {{ __('landing-page.pricing.title_line1') }}
                <span class="gradient-text">{{ __('landing-page.pricing.title_gradient') }}</span>
            </h2>
            <p class="text-lg text-gray-600">
                {{ __('landing-page.pricing.subtitle') }}
            </p>
        </div>

        <!-- Billing Toggle -->
        <div class="flex items-center justify-center gap-4 mb-12">
            <span class="text-sm font-medium" :class="annual ? 'text-gray-400' : 'text-gray-900'">{{ __('landing-page.pricing.billing_monthly') }}</span>

            <button
                @click="annual = !annual"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                :class="annual ? 'bg-gray-900' : 'bg-gray-300'"
            >
                <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="annual ? 'translate-x-6' : 'translate-x-1'"
                ></span>
            </button>

            <span class="text-sm font-medium" :class="annual ? 'text-gray-900' : 'text-gray-400'">
                {{ __('landing-page.pricing.billing_annual') }}
                <span class="ml-1.5 inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">{{ __('landing-page.pricing.save_badge') }}</span>
            </span>
        </div>

        <!-- Pricing Grid -->
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">

            <!-- Hobby -->
            <div class="glass-card p-8 relative">
                <div class="text-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $p['hobby']['name'] }}</h3>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-4xl font-bold text-gray-900">{{ $p['hobby']['price'] }}</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">{{ $p['hobby']['description'] }}</p>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach($p['hobby']['features'] as $feature)
                        <li class="flex items-center gap-3">
                            <x-lucide-check class="w-5 h-5 text-gray-900 flex-shrink-0" />
                            <span class="text-sm text-gray-600">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full block text-center btn-secondary">{{ $p['hobby']['cta'] }}</a>
                @else
                    <a href="{{ route('register') }}" class="w-full block text-center btn-secondary">{{ $p['hobby']['cta'] }}</a>
                @endauth
            </div>

            <!-- Pro -->
            <div class="glass-card p-8 relative border-gray-900/50">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="px-3 py-1 rounded-full bg-gray-900 text-white text-xs font-medium">{{ $p['pro']['badge'] }}</span>
                </div>
                <div class="text-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $p['pro']['name'] }}</h3>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-4xl font-bold text-gray-900" x-text="annual ? '{{ $p['pro']['price_annual'] }}' : '{{ $p['pro']['price_monthly'] }}'">{{ $p['pro']['price_monthly'] }}</span>
                        <span class="text-gray-600" x-text="annual ? '{{ $p['pro']['period_annual'] }}' : '{{ $p['pro']['period_monthly'] }}'">{{ $p['pro']['period_monthly'] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" x-show="annual">{{ $p['pro']['annual_equivalent'] }}</p>
                    <p class="text-sm text-gray-600 mt-2">{{ $p['pro']['description'] }}</p>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach($p['pro']['features'] as $feature)
                        <li class="flex items-center gap-3">
                            <x-lucide-check class="w-5 h-5 text-gray-900 flex-shrink-0" />
                            <span class="text-sm text-gray-600">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full block text-center btn-primary">{{ $p['pro']['cta'] }}</a>
                @else
                    <a href="{{ route('register') }}" class="w-full block text-center btn-primary">{{ $p['pro']['cta'] }}</a>
                @endauth
            </div>

            <!-- Team -->
            <div class="glass-card p-8 relative">
                <div class="text-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $p['team']['name'] }}</h3>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-4xl font-bold text-gray-900" x-text="annual ? '{{ $p['team']['price_annual'] }}' : '{{ $p['team']['price_monthly'] }}'">{{ $p['team']['price_monthly'] }}</span>
                        <span class="text-gray-600" x-text="annual ? '{{ $p['team']['period_annual'] }}' : '{{ $p['team']['period_monthly'] }}'">{{ $p['team']['period_monthly'] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" x-show="annual">{{ $p['team']['annual_equivalent'] }}</p>
                    <p class="text-sm text-gray-600 mt-2">{{ $p['team']['description'] }}</p>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach($p['team']['features'] as $feature)
                        <li class="flex items-center gap-3">
                            <x-lucide-check class="w-5 h-5 text-gray-900 flex-shrink-0" />
                            <span class="text-sm text-gray-600">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full block text-center btn-secondary">{{ $p['team']['cta'] }}</a>
                @else
                    <a href="{{ route('register') }}" class="w-full block text-center btn-secondary">{{ $p['team']['cta'] }}</a>
                @endauth
            </div>

        </div>
    </div>
</section>
