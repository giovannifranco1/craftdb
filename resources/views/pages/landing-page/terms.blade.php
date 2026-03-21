@php
    $legalDate = now()->locale(app()->getLocale())->translatedFormat(__('landing-page-legal.date_format'));
    $s = __('landing-page-legal.terms.sections');
@endphp
<x-layouts.landing-page :title="__('landing-page-legal.terms.meta_title')">
    <div class="pt-24 pb-16">
        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                    {{ __('landing-page-legal.terms.heading') }}
                </h1>
                <p class="text-gray-500 text-sm mb-12">
                    {{ __('landing-page-legal.terms.last_updated', ['date' => $legalDate]) }}
                </p>

                <div class="prose prose-gray max-w-none space-y-10 text-gray-600">
                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['acceptance']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['acceptance']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['description']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['description']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['account']['title'] }}</h2>
                        <p class="leading-relaxed mb-4">{{ $s['account']['p1'] }}</p>
                        <p class="leading-relaxed">{{ $s['account']['p2'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['acceptable_use']['title'] }}</h2>
                        <p class="leading-relaxed mb-4">{{ $s['acceptable_use']['intro'] }}</p>
                        <ul class="list-disc pl-6 space-y-2">
                            @foreach($s['acceptable_use']['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['ip']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['ip']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['liability']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['liability']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['changes']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['changes']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['contact']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['contact']['body'] }}</p>
                    </section>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <a href="{{ route('landing.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors">
                        {{ __('landing-page-legal.terms.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.landing-page>
