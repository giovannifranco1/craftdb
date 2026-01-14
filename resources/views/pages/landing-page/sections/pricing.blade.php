<section id="pricing" class="py-24 relative section-gradient scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-6">
                Preços simples, <span class="bg-gradient-to-r from-blue-600 via-cyan-500 to-blue-600 bg-clip-text text-transparent">sem surpresas</span>
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
                        'description' => 'Perfeito para projetos pessoais e aprendizado.',
                        'features' => [
                            '3 diagramas',
                            '1 workspace',
                            'Temas básicos',
                            'Exportação PNG',
                            'Suporte por email',
                        ],
                        'cta' => 'Começar grátis',
                        'popular' => false,
                    ],
                    [
                        'name' => 'Pro',
                        'price' => 'R$29',
                        'period' => '/mês',
                        'description' => 'Para desenvolvedores e freelancers que precisam de mais.',
                        'features' => [
                            'Diagramas ilimitados',
                            'Workspaces ilimitados',
                            'Todos os temas',
                            'Exportação SQL + PNG',
                            'Colaboração em tempo real',
                            'Histórico de versões',
                            'Sticky notes',
                            'Suporte prioritário',
                        ],
                        'cta' => 'Assinar Pro',
                        'popular' => true,
                    ],
                    [
                        'name' => 'Team',
                        'price' => 'R$79',
                        'period' => '/mês',
                        'description' => 'Para times que precisam de colaboração avançada.',
                        'features' => [
                            'Tudo do Pro',
                            'Permissões granulares',
                            'SSO (SAML/OIDC)',
                            'Audit logs',
                            'API access',
                            'Integração com Git',
                            'Suporte dedicado',
                            'SLA 99.9%',
                        ],
                        'cta' => 'Falar com vendas',
                        'popular' => false,
                    ],
                ];
            @endphp

            @foreach($plans as $plan)
                <div class="bg-white p-8 rounded-xl border-2 {{ $plan['popular'] ? 'landing-primary-border relative shadow-lg' : 'border-gray-200' }} landing-card-hover">
                    @if($plan['popular'])
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="landing-primary-bg text-white px-3 py-1 rounded-full text-xs font-semibold">
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
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 landing-primary-color flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-sm text-gray-700">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @auth
                        <a href="{{ route('dashboard') }}" class="block w-full text-center px-5 py-2.5 {{ $plan['popular'] ? 'landing-btn-primary' : 'landing-btn-outline border-2' }} rounded-lg text-sm font-medium transition">
                            {{ $plan['cta'] }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="block w-full text-center px-5 py-2.5 {{ $plan['popular'] ? 'landing-btn-primary' : 'landing-btn-outline border-2' }} rounded-lg text-sm font-medium transition">
                            {{ $plan['cta'] }}
                        </a>
                    @endauth
                </div>
            @endforeach
        </div>
    </div>
</section>
