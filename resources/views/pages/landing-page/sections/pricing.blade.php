<section id="pricing" class="py-24 relative section-gradient scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Preços simples,
                <span class="gradient-text">sem surpresas</span>
            </h2>
            <p class="text-lg text-gray-600">
                Comece grátis e escale conforme sua necessidade.
                Sem cartão de crédito para começar.
            </p>
        </div>

        <!-- Pricing Grid -->
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            @php
                $plans = [
                    [
                        'name' => 'Hobby',
                        'price' => 'Grátis',
                        'period' => '',
                        'description' => 'Para projetos pessoais e aprendizado.',
                        'features' => [
                            '3 diagramas',
                            '1 time com até 2 membros',
                            'Links públicos de diagrama',
                            'Suporte por email',
                        ],
                        'cta' => 'Começar grátis',
                        'popular' => false,
                    ],
                    [
                        'name' => 'Pro',
                        'price' => 'R$49,90',
                        'period' => '/mês',
                        'description' => 'Para devs e freelancers que precisam de mais.',
                        'features' => [
                            'Diagramas ilimitados',
                            'Tabelas ilimitadas por diagrama',
                            'Import SQL / Laravel Migrations',
                            'Links públicos de diagrama',
                            'Áreas, Sticky Notes, Graph View',
                            '1 time com até 3 membros',
                            'Suporte prioritário',
                        ],
                        'cta' => 'Assinar Pro',
                        'popular' => true,
                    ],
                    [
                        'name' => 'Team',
                        'price' => 'R$99',
                        'period' => '/mês',
                        'description' => 'Para times que trabalham juntos em schemas.',
                        'features' => [
                            'Tudo do Pro',
                            'Membros ilimitados no time',
                            'Múltiplos times',
                            'Permissões por membro (leitor, editor, dono)',
                            'Histórico de visualizações',
                            'Suporte dedicado',
                        ],
                        'cta' => 'Assinar Team',
                        'popular' => false,
                    ],
                ];
            @endphp

            @foreach($plans as $plan)
                <div class="glass-card p-8 relative {{ $plan['popular'] ? 'border-gray-900/50' : '' }}">
                    @if($plan['popular'])
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="px-3 py-1 rounded-full bg-gray-900 text-white text-xs font-medium">
                                Mais popular
                            </span>
                        </div>
                    @endif

                    <div class="text-center mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            {{ $plan['name'] }}
                        </h3>
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-4xl font-bold text-gray-900">
                                {{ $plan['price'] }}
                            </span>
                            @if($plan['period'])
                                <span class="text-gray-600">{{ $plan['period'] }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-2">
                            {{ $plan['description'] }}
                        </p>
                    </div>

                    <ul class="space-y-3 mb-8">
                        @foreach($plan['features'] as $feature)
                            <li class="flex items-center gap-3">
                                <x-lucide-check class="w-5 h-5 text-gray-900 flex-shrink-0" />
                                <span class="text-sm text-gray-600">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full block text-center {{ $plan['popular'] ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $plan['cta'] }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full block text-center {{ $plan['popular'] ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $plan['cta'] }}
                        </a>
                    @endauth
                </div>
            @endforeach
        </div>
    </div>
</section>
