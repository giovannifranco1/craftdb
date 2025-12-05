<x-layouts.landing-page title="CraftDB - Crie diagramas de banco de dados com arte e precisão">
    <!-- Hero Section -->
    <section class="pt-28 pb-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-bold mb-5 text-gray-900 leading-tight">
                    <span class="block mt-2 landing-primary-color">{{ $site['tagline'] ?? 'com arte e precisão' }}</span>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
                    {{ $site['description'] ?? 'CraftDB é uma aplicação web moderna para criação, visualização e gerenciamento de diagramas ER de forma visual e interativa.' }}
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                    @auth
                        <a href="{{ route('home') }}" class="px-6 py-3 landing-btn-primary text-white text-sm font-medium rounded-lg transition shadow-sm">
                            Ir para Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-6 py-3 landing-btn-primary text-white text-sm font-medium rounded-lg transition shadow-sm">
                            Começar Agora
                        </a>
                        <a href="#features" class="px-6 py-3 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 landing-btn-outline transition">
                            Ver Features
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Hero Image/Illustration -->
            <div class="max-w-5xl mx-auto">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-8 border border-blue-100">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                            </div>
                            <span class="text-xs text-gray-500 font-medium">Diagram Editor</span>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                <div class="font-semibold text-sm text-blue-900 mb-2">users</div>
                                <div class="text-xs text-blue-700 space-y-1">
                                    <div>id (PK)</div>
                                    <div>name</div>
                                    <div>email</div>
                                </div>
                            </div>
                            <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                                <div class="font-semibold text-sm text-indigo-900 mb-2">posts</div>
                                <div class="text-xs text-indigo-700 space-y-1">
                                    <div>id (PK)</div>
                                    <div>user_id (FK)</div>
                                    <div>title</div>
                                </div>
                            </div>
                            <div class="bg-cyan-50 rounded-lg p-4 border border-cyan-100">
                                <div class="font-semibold text-sm text-cyan-900 mb-2">comments</div>
                                <div class="text-xs text-cyan-700 space-y-1">
                                    <div>id (PK)</div>
                                    <div>post_id (FK)</div>
                                    <div>content</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-center space-x-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 landing-primary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            <span>Relacionamentos visuais interativos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Preview -->
    <section id="features" class="py-16 bg-gray-50"
             x-data="{
                 visible: false,
                 init() {
                     const observer = new IntersectionObserver((entries) => {
                         entries.forEach(entry => {
                             if (entry.isIntersecting && !this.visible) {
                                 this.visible = true;

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
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-3 text-gray-900">Features Principais</h2>
                <p class="text-base text-gray-600 max-w-xl mx-auto">
                    Tudo que você precisa para criar e gerenciar diagramas de banco de dados profissionais
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach(array_slice($features ?? [], 0, 4) as $index => $feature)
                <div class="landing-feature-card bg-white p-6 rounded-lg border border-gray-200 landing-card-hover group">
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
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 group-hover:landing-primary-color transition-colors">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('landing.features') }}" class="landing-primary-color hover:landing-primary-hover font-medium text-base inline-flex items-center transition">
                    Ver todas as features
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-16 bg-gray-50"
             x-data="{
                 visible: false,
                 init() {
                     const observer = new IntersectionObserver((entries) => {
                         entries.forEach(entry => {
                             if (entry.isIntersecting && !this.visible) {
                                 this.visible = true;
                                 const steps = entry.target.querySelectorAll('.landing-step-item');
                                 steps.forEach((step, index) => {
                                     setTimeout(() => {
                                         step.style.opacity = '1';
                                         step.style.transform = 'translateY(0)';
                                     }, index * 100);
                                 });
                             }
                         });
                     }, { threshold: 0.2, rootMargin: '0px 0px -100px 0px' });

                     observer.observe(this.$el);
                 }
             }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-3 text-gray-900">Como Funciona</h2>
                <p class="text-base text-gray-600 max-w-xl mx-auto">
                    Crie diagramas profissionais em poucos passos
                </p>
            </div>

            <div class="space-y-6">
                @foreach($howItWorks ?? [] as $index => $step)
                <div class="landing-step-item bg-white rounded-xl border border-gray-200 p-6 md:p-8 landing-card-hover">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <!-- Step Number -->
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 landing-icon-bg rounded-xl flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                                {{ $step['step'] }}
                            </div>
                        </div>

                        <!-- Step Content -->
                        <div class="flex-1">
                            <h3 class="text-xl font-bold mb-2 text-gray-900">{{ $step['title'] }}</h3>
                            <p class="text-base text-gray-600 leading-relaxed">{{ $step['description'] }}</p>
                        </div>

                        <!-- Step Icon -->
                        <div class="flex-shrink-0">
                            @if($step['step'] == 1)
                                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 landing-primary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                            @elseif($step['step'] == 2)
                                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 landing-primary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @elseif($step['step'] == 3)
                                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 landing-primary-color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 landing-cta-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                Pronto para começar?
            </h2>
            <p class="text-lg landing-cta-text mb-8">
                Junte-se a desenvolvedores e arquitetos que já estão criando diagramas profissionais com CraftDB.
            </p>
            @auth
                <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-white landing-primary-color rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-lg">
                    Ir para Dashboard
                </a>
            @else
                <a href="{{ route('register') }}" class="inline-block px-6 py-3 bg-white landing-primary-color rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-lg">
                    Começar Grátis Agora
                </a>
            @endauth
        </div>
    </section>
</x-layouts.landing-page>

