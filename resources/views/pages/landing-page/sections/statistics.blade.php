<section class="py-20 bg-gray-100/30 scroll-animate">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            @php
                $stats = [
                    [
                        'icon' => 'users',
                        'value' => '5.000+',
                        'label' => 'Desenvolvedores ativos',
                    ],
                    [
                        'icon' => 'code',
                        'value' => '50.000+',
                        'label' => 'Diagramas criados',
                    ],
                    [
                        'icon' => 'clock',
                        'value' => '99.9%',
                        'label' => 'Uptime garantido',
                    ],
                    [
                        'icon' => 'star',
                        'value' => '4.9',
                        'label' => 'Avaliação média',
                    ],
                ];
            @endphp

            @foreach($stats as $index => $stat)
                <div class="text-center group">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-900/10 text-gray-900 mb-4 group-hover:scale-110 transition-transform duration-300">
                        @if($stat['icon'] === 'users')
                            <x-lucide-users class="w-6 h-6" />
                        @elseif($stat['icon'] === 'code')
                            <x-lucide-code class="w-6 h-6" />
                        @elseif($stat['icon'] === 'clock')
                            <x-lucide-clock class="w-6 h-6" />
                        @elseif($stat['icon'] === 'star')
                            <x-lucide-star class="w-6 h-6" />
                        @endif
                    </div>
                    <div class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                        {{ $stat['value'] }}
                    </div>
                    <div class="text-sm text-gray-600">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
