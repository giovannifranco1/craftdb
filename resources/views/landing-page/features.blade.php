<x-layouts.landing-page title="Features - CraftDB">
    <!-- Hero Section -->
    <section class="pt-28 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                <span class="landing-primary-color">Features</span> do CraftDB
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Descubra todas as funcionalidades que tornam o CraftDB a melhor ferramenta para criar diagramas de banco de dados
            </p>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-16"
             x-data="{
                 visible: false,
                 init() {
                     const observer = new IntersectionObserver((entries) => {
                         entries.forEach(entry => {
                             if (entry.isIntersecting && !this.visible) {
                                 this.visible = true;
                                 // Anima todos os cards quando a seção entra na viewport
                                 const cards = entry.target.querySelectorAll('.landing-feature-card');
                                 cards.forEach((card, index) => {
                                     setTimeout(() => {
                                         card.style.opacity = '1';
                                         card.style.transform = 'translateY(0)';
                                     }, index * 100);
                                 });
                             }
                         });
                     }, { threshold: 0.2, rootMargin: '0px 0px -100px 0px' });

                     observer.observe(this.$el);
                 }
             }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $featuresList = $features ?? config('landingpage.features', []);
            @endphp

            @if(empty($featuresList))
                <div class="text-center py-12">
                    <p class="text-gray-600">Nenhuma feature encontrada.</p>
                </div>
            @else
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuresList as $index => $feature)
                <div class="landing-feature-card bg-white p-6 rounded-xl border border-gray-200 landing-card-hover group">
                    <div class="w-12 h-12 landing-icon-bg rounded-lg flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        @if($feature['icon'] === 'palette')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                            </svg>
                        @elseif($feature['icon'] === 'table')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'project-diagram')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        @elseif($feature['icon'] === 'chart-network')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'users')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'heart')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'upload')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        @elseif($feature['icon'] === 'download')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                        @elseif($feature['icon'] === 'lock')
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold mb-2.5 text-gray-900 group-hover:landing-primary-color transition-colors">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $feature['description'] }}
                        @if(isset($feature['details']))
                            <span class="block mt-2 text-xs text-gray-500">{{ $feature['details'] }}</span>
                        @endif
                    </p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 landing-cta-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                Pronto para experimentar?
            </h2>
            <p class="text-lg landing-cta-text mb-8">
                Comece a criar diagramas profissionais hoje mesmo
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
