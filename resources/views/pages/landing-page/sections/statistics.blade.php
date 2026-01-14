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
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-600/10 text-blue-600 mb-4 group-hover:scale-110 transition-transform duration-300">
                        @if($stat['icon'] === 'users')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        @elseif($stat['icon'] === 'code')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        @elseif($stat['icon'] === 'clock')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @elseif($stat['icon'] === 'star')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
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
