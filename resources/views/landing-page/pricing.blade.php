<x-layouts.landing-page title="Pricing - CraftDB">
    <!-- Hero Section -->
    <section class="pt-28 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                Planos e <span class="landing-primary-color">Preços</span>
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Escolha o plano ideal para suas necessidades. Todos os planos incluem acesso completo às funcionalidades principais.
            </p>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6"
                 x-data="{
                     init() {
                         const observer = new IntersectionObserver((entries) => {
                             entries.forEach((entry, index) => {
                                 if (entry.isIntersecting) {
                                     setTimeout(() => {
                                         entry.target.classList.remove('opacity-0', 'translate-y-8');
                                         entry.target.classList.add('opacity-100', 'translate-y-0');
                                     }, index * 150);
                                 }
                             });
                         }, { threshold: 0.1 });

                         this.$el.querySelectorAll('.landing-pricing-card').forEach(card => {
                             observer.observe(card);
                         });
                     }
                 }">
                @foreach($plans ?? [] as $index => $plan)
                <div class="landing-pricing-card bg-white p-6 rounded-xl border-2 {{ $plan['popular'] ? 'landing-primary-border relative shadow-lg' : 'border-gray-200' }} landing-card-hover opacity-0 translate-y-8 transition-all duration-700 ease-out"
                     style="transition-delay: {{ $index * 150 }}ms">
                    @if($plan['popular'] ?? false)
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <span class="landing-primary-bg text-white px-3 py-1 rounded-full text-xs font-semibold">
                            Mais Popular
                        </span>
                    </div>
                    @endif
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold mb-2 text-gray-900">{{ $plan['name'] }}</h3>
                        <div class="mb-3">
                            @if(isset($plan['price_text']))
                                <span class="text-3xl font-bold text-gray-900">{{ $plan['price_text'] }}</span>
                            @else
                                <span class="text-3xl font-bold {{ $plan['popular'] ? 'landing-primary-color' : 'text-gray-900' }}">{{ $plan['currency'] }}{{ $plan['price'] }}</span>
                                <span class="text-gray-600 text-sm">/{{ $plan['period'] }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600">{{ $plan['description'] }}</p>
                    </div>
                    <ul class="space-y-3 mb-6">
                        @foreach($plan['features'] as $feature)
                        <li class="flex items-start text-sm">
                            <svg class="w-5 h-5 landing-primary-color mr-2.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @if(isset($plan['cta_link']))
                        <a href="{{ $plan['cta_link'] }}" class="block w-full text-center px-5 py-2.5 border-2 border-gray-300 rounded-lg text-sm font-medium landing-btn-outline transition">
                            {{ $plan['cta_text'] }}
                        </a>
                    @else
                        @auth
                            <a href="{{ route('home') }}" class="block w-full text-center px-5 py-2.5 {{ $plan['popular'] ? 'landing-btn-primary' : 'landing-btn-outline border-2' }} rounded-lg text-sm font-medium transition">
                                {{ $plan['name'] === 'Free' ? 'Você já está usando' : 'Atualizar Plano' }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="block w-full text-center px-5 py-2.5 {{ $plan['popular'] ? 'landing-btn-primary' : 'landing-btn-outline border-2' }} rounded-lg text-sm font-medium transition">
                                {{ $plan['cta_text'] }}
                            </a>
                        @endauth
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold mb-3 text-gray-900">Perguntas Frequentes</h2>
            </div>
            <div class="space-y-4">
                @foreach($faq ?? [] as $item)
                <div class="bg-white p-5 rounded-lg border border-gray-200 landing-card-hover">
                    <h3 class="text-base font-semibold mb-2 text-gray-900">{{ $item['question'] }}</h3>
                    <p class="text-sm text-gray-600">{{ $item['answer'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 landing-cta-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                Comece hoje mesmo
            </h2>
            <p class="text-lg landing-cta-text mb-8">
                Experimente gratuitamente e veja como o CraftDB pode transformar seu workflow
            </p>
            @auth
                <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-white landing-primary-color rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-lg">
                    Ir para Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-block px-6 py-3 bg-white landing-primary-color rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-lg">
                    Começar Grátis
                </a>
            @endauth
        </div>
    </section>
</x-layouts.landing-page>

