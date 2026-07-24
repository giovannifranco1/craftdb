<section id="showcase" class="py-24 relative scroll-animate overflow-hidden">
    <div class="container mx-auto px-6">
        {{-- Section Header --}}
        <div class="max-w-3xl mx-auto text-center mb-16 md:mb-20">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                {{ __('landing-page.showcase.title_line1') }}
                <span class="gradient-text">{{ __('landing-page.showcase.title_gradient') }}</span>
            </h2>
            <p class="text-lg text-gray-600">
                {{ __('landing-page.showcase.subtitle') }}
            </p>
        </div>

        {{-- Showcase items (lado do mockup alterna a cada item) --}}
        <div class="space-y-20 lg:space-y-28">
            @foreach(__('landing-page.showcase.items') as $index => $item)
                @php($mockupRight = $index % 2 === 0)

                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center fade-in-up">
                    {{-- Texto --}}
                    <div class="{{ $mockupRight ? 'lg:order-1' : 'lg:order-2' }}">
                        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                            {{ $item['title'] }}
                        </h3>
                        <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                            {{ $item['description'] }}
                        </p>
                        <ul class="space-y-3">
                            @foreach($item['bullets'] as $bullet)
                                <li class="flex items-start gap-3 text-gray-700">
                                    <x-lucide-check class="w-5 h-5 text-gray-900 shrink-0 mt-0.5" />
                                    <span>{{ $bullet }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Mockup do MacBook (imagem trocável em landing-page.showcase.items.*.image) --}}
                    <x-landing-page.macbook-mockup
                        :screen="$item['image']"
                        :alt="$item['alt']"
                        class="{{ $mockupRight ? 'lg:order-2' : 'lg:order-1' }}"
                    />
                </div>
            @endforeach
        </div>
    </div>
</section>
