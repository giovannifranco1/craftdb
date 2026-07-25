{{-- Intro do bloco showcase --}}
<section id="showcase" class="pt-24 pb-8 md:pb-12 relative overflow-hidden scroll-animate">
    <div class="container mx-auto px-6">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                {{ __('landing-page.showcase.title_line1') }}
                <span class="gradient-text">{{ __('landing-page.showcase.title_gradient') }}</span>
            </h2>
            <p class="text-lg text-gray-600">
                {{ __('landing-page.showcase.subtitle') }}
            </p>
        </div>
    </div>
</section>

{{-- Uma section por foto: texto centralizado no topo + mockup bem grande abaixo --}}
@foreach(__('landing-page.showcase.items') as $index => $item)
    <section
        id="showcase-{{ $index + 1 }}"
        class="py-16 md:py-24 relative overflow-hidden scroll-animate {{ $index % 2 === 1 ? 'bg-gray-50' : '' }}"
    >
        <div class="container mx-auto px-6">
            {{-- Texto --}}
            <div class="max-w-3xl mx-auto text-center mb-10 md:mb-14">
                <h3 class="text-2xl md:text-4xl font-bold text-gray-900 mb-4">
                    {{ $item['title'] }}
                </h3>
                <p class="text-lg md:text-xl text-gray-600 mb-6 leading-relaxed">
                    {{ $item['description'] }}
                </p>
                <ul class="flex flex-wrap justify-center gap-x-6 gap-y-2">
                    @foreach($item['bullets'] as $bullet)
                        <li class="inline-flex items-center gap-2 text-gray-700">
                            <x-lucide-check class="w-5 h-5 text-gray-900 shrink-0" />
                            <span>{{ $bullet }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Mockup do MacBook — bem grande (imagem trocável em landing-page.showcase.items.*.image) --}}
            <x-landing-page.macbook-mockup
                :screen="$item['image']"
                :alt="$item['alt']"
                class="w-full max-w-5xl lg:max-w-6xl xl:max-w-7xl mx-auto zoom-on-scroll"
            />
        </div>
    </section>
@endforeach
