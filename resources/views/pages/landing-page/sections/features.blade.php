<section id="features" class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                {{ __('landing-page.features.title_line1') }}
                <span class="gradient-text">{{ __('landing-page.features.title_gradient') }}</span>
                {{ __('landing-page.features.title_line2') }}
            </h2>
            <p class="text-lg text-gray-600">
                {{ __('landing-page.features.subtitle') }}
            </p>
        </div>

        <!-- Features Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(__('landing-page.features.items') as $feature)
                <div class="glass-card p-6 group hover:border-gray-900/30 transition-all duration-300">
                    <div class="feature-icon mb-4 group-hover:scale-110 transition-transform duration-300">
                        @if($feature['icon'] === 'add-table')
                            <flux:icon.add-table class="w-5 h-5" />
                        @elseif($feature['icon'] === 'graph')
                            <flux:icon.graph class="w-5 h-5" />
                        @elseif($feature['icon'] === 'add-area')
                            <flux:icon.add-area class="w-5 h-5" />
                        @elseif($feature['icon'] === 'add-note')
                            <flux:icon.add-note class="w-5 h-5" />
                        @elseif($feature['icon'] === 'palette')
                            <x-lucide-palette class="w-5 h-5" />
                        @elseif($feature['icon'] === 'users')
                            <x-lucide-users class="w-5 h-5" />
                        @elseif($feature['icon'] === 'code')
                            <x-lucide-file-code class="w-5 h-5" />
                        @elseif($feature['icon'] === 'share')
                            <x-lucide-share-2 class="w-5 h-5" />
                        @elseif($feature['icon'] === 'heart')
                            <x-lucide-heart class="w-5 h-5" />
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $feature['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
