@php
    $legalDate = now()->locale(app()->getLocale())->translatedFormat(__('landing-page-legal.date_format'));
    $s = __('landing-page-legal.policy.sections');
@endphp
<x-layouts.landing-page :title="__('landing-page-legal.policy.meta_title')">
    <div class="pt-24 pb-16">
        <div class="container mx-auto px-6">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                    {{ __('landing-page-legal.policy.heading') }}
                </h1>
                <p class="text-gray-500 text-sm mb-12">
                    {{ __('landing-page-legal.policy.last_updated', ['date' => $legalDate]) }}
                </p>

                <div class="prose prose-gray max-w-none space-y-10 text-gray-600">
                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['intro']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['intro']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['data_collected']['title'] }}</h2>
                        <p class="leading-relaxed mb-4">{{ $s['data_collected']['intro'] }}</p>
                        <ul class="list-disc pl-6 space-y-2">
                            @foreach($s['data_collected']['items'] as $item)
                                <li>{!! $item !!}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['how_we_use']['title'] }}</h2>
                        <p class="leading-relaxed mb-4">{{ $s['how_we_use']['intro'] }}</p>
                        <ul class="list-disc pl-6 space-y-2">
                            @foreach($s['how_we_use']['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['sharing']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['sharing']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['retention']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['retention']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['rights']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['rights']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['cookies']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['cookies']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['policy_changes']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['policy_changes']['body'] }}</p>
                    </section>

                    <section>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ $s['contact']['title'] }}</h2>
                        <p class="leading-relaxed">{{ $s['contact']['body'] }}</p>
                    </section>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-200">
                    <a href="{{ route('landing.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors">
                        {{ __('landing-page-legal.policy.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.landing-page>
