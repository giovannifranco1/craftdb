<section id="faq" class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                {{ __('landing-page.faq.title_line1') }}<br />
                <span class="gradient-text">{{ __('landing-page.faq.title_gradient') }}</span>
            </h2>
            <p class="text-lg text-gray-600">
                {{ __('landing-page.faq.subtitle') }}
            </p>
        </div>

        <!-- FAQ List -->
        <div class="max-w-3xl mx-auto space-y-4" x-data="{ openIndex: 0 }">
            @foreach(__('landing-page.faq.items') as $index => $faq)
                <div class="glass-card overflow-hidden">
                    <button
                        @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                        class="w-full px-6 py-4 flex items-center justify-between gap-3 text-left"
                    >
                        <span class="font-medium text-gray-900">{{ $faq['question'] }}</span>
                        <span class="inline-flex shrink-0 transition-transform duration-300" x-bind:class="{ 'rotate-180': openIndex === {{ $index }} }">
                            <x-lucide-chevron-down class="w-5 h-5 text-gray-600" />
                        </span>
                    </button>
                    <div
                        x-show="openIndex === {{ $index }}"
                        class="px-6 pb-4"
                    >
                        <p class="text-gray-600 leading-relaxed">
                            {{ $faq['answer'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
