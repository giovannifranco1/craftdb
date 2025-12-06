<x-layouts.landing-page title="CraftDB - Crie diagramas de banco de dados com arte e precisão">
    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center relative overflow-hidden pt-16" style="background-image: url('{{ Vite::asset('resources/images/landing-page/background-blue-gray.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <!-- Overlay para melhorar legibilidade do texto -->
        <div class="absolute inset-0 bg-white/40"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full py-8">
            <div class="text-center mb-8 md:mb-12">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-gray-900 leading-tight">
                    <span class="block mt-2 landing-primary-color">{{ $site['tagline'] ?? 'com arte e precisão' }}</span>
                </h1>
                <p class="text-base md:text-lg text-gray-600 max-w-2xl mx-auto mb-6">
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
                    @endauth
                </div>
            </div>

            <!-- Hero Image -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 md:p-6 border border-blue-100">
                    <div class="bg-white rounded-lg p-3 md:p-4 shadow-sm border border-gray-200 overflow-hidden">
                        <img src="{{ Vite::asset('resources/images/landing-page/print-dashboard.png') }}"
                             alt="CraftDB Dashboard - Diagrama de banco de dados"
                             class="w-full h-auto rounded-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose CraftDB -->
    <section class="pt-32 pb-16 bg-white relative -mt-32 z-20"
             x-data="{
                 visible: false,
                 init() {
                     const observer = new IntersectionObserver((entries) => {
                         entries.forEach(entry => {
                             if (entry.isIntersecting && !this.visible) {
                                 this.visible = true;
                                 const cards = entry.target.querySelectorAll('.why-choose-card');
                                 cards.forEach((card, index) => {
                                     setTimeout(() => {
                                         card.style.opacity = '1';
                                         card.style.transform = 'translateY(0)';
                                     }, index * 150);
                                 });
                             }
                         });
                     }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

                     observer.observe(this.$el);
                 }
             }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-3 text-gray-900">Por que escolher o CraftDB?</h2>
                <p class="text-base text-gray-600 max-w-xl mx-auto">
                    Descubra as vantagens que fazem do CraftDB a melhor escolha para seus projetos
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="why-choose-card bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100 landing-card-hover group opacity-0 transform translate-y-8 transition-all duration-500">
                    <div class="w-12 h-12 landing-primary-bg rounded-lg flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 group-hover:landing-primary-color transition-colors">
                        Interface Intuitiva
                    </h3>
                    <p class="text-sm text-gray-600 mb-3 font-medium">
                        Design moderno e fácil de usar
                    </p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Crie diagramas profissionais sem precisar de conhecimento técnico avançado. Nossa interface foi pensada para ser intuitiva e eficiente.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="why-choose-card bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-xl border border-purple-100 landing-card-hover group opacity-0 transform translate-y-8 transition-all duration-500">
                    <div class="w-12 h-12 landing-primary-bg rounded-lg flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 group-hover:landing-primary-color transition-colors">
                        Segurança e Confiabilidade
                    </h3>
                    <p class="text-sm text-gray-600 mb-3 font-medium">
                        Seus dados protegidos e seguros
                    </p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Priorizamos a segurança dos seus projetos. Todos os dados são criptografados e armazenados com os mais altos padrões de segurança.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="why-choose-card bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-xl border border-green-100 landing-card-hover group opacity-0 transform translate-y-8 transition-all duration-500">
                    <div class="w-12 h-12 landing-primary-bg rounded-lg flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 group-hover:landing-primary-color transition-colors">
                        Economia de Tempo
                    </h3>
                    <p class="text-sm text-gray-600 mb-3 font-medium">
                        Crie diagramas em minutos, não horas
                    </p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Automatize processos repetitivos e foque no que realmente importa. Nossa ferramenta acelera seu fluxo de trabalho significativamente.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="why-choose-card bg-gradient-to-br from-orange-50 to-amber-50 p-6 rounded-xl border border-orange-100 landing-card-hover group opacity-0 transform translate-y-8 transition-all duration-500">
                    <div class="w-12 h-12 landing-primary-bg rounded-lg flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 group-hover:landing-primary-color transition-colors">
                        Colaboração em Equipe
                    </h3>
                    <p class="text-sm text-gray-600 mb-3 font-medium">
                        Trabalhe junto com sua equipe
                    </p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Compartilhe diagramas facilmente e colabore em tempo real. Mantenha toda sua equipe sincronizada e produtiva.
                    </p>
                </div>

                <!-- Card 5 -->
                <div class="why-choose-card bg-gradient-to-br from-cyan-50 to-blue-50 p-6 rounded-xl border border-cyan-100 landing-card-hover group opacity-0 transform translate-y-8 transition-all duration-500">
                    <div class="w-12 h-12 landing-primary-bg rounded-lg flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 group-hover:landing-primary-color transition-colors">
                        Atualizações Constantes
                    </h3>
                    <p class="text-sm text-gray-600 mb-3 font-medium">
                        Sempre evoluindo com novas funcionalidades
                    </p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Nosso produto está em constante evolução. Adicionamos regularmente novas features baseadas no feedback dos usuários.
                    </p>
                </div>

                <!-- Card 6 -->
                <div class="why-choose-card bg-gradient-to-br from-indigo-50 to-purple-50 p-6 rounded-xl border border-indigo-100 landing-card-hover group opacity-0 transform translate-y-8 transition-all duration-500">
                    <div class="w-12 h-12 landing-primary-bg rounded-lg flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-900 group-hover:landing-primary-color transition-colors">
                        Resultados Profissionais
                    </h3>
                    <p class="text-sm text-gray-600 mb-3 font-medium">
                        Diagramas de alta qualidade
                    </p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Gere diagramas ER profissionais e precisos que impressionam clientes e facilitam a comunicação com sua equipe de desenvolvimento.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Target Audience / Personas -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">Feito para você</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    CraftDB é perfeito para desenvolvedores, arquitetos e equipes que precisam criar e gerenciar diagramas de banco de dados de forma profissional
                </p>
            </div>

            <div class="relative"
                 x-data="{
                     init() {
                         const observer = new IntersectionObserver((entries) => {
                             entries.forEach(entry => {
                                 if (entry.isIntersecting) {
                                     const cards = entry.target.querySelectorAll('.persona-card');
                                     cards.forEach((card, index) => {
                                         setTimeout(() => {
                                             card.style.opacity = '1';
                                             card.style.transform = 'translate(0, 0) scale(1)';
                                         }, index * 400);
                                     });
                                     observer.unobserve(entry.target);
                                 }
                             });
                         }, { threshold: 0.1 });

                         observer.observe(this.$el);
                     }
                 }">
                <!-- Z-Layout: Card 1 - Topo Esquerdo -->
                <div class="persona-card opacity-0 -translate-x-16 translate-y-4 scale-95 transition-all duration-700 ease-out mb-6 flex justify-start">
                    <div class="max-w-2xl p-6 flex gap-6 rounded-lg transition-all duration-300">
                        <div class="flex-shrink-0 w-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center self-stretch">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">Desenvolvedor Full-Stack</h3>
                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                Ideal para desenvolvedores que trabalham com frontend e backend, precisando visualizar e documentar a estrutura de seus bancos de dados de forma clara e eficiente.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Z-Layout: Card 2 - Topo Direito -->
                <div class="persona-card opacity-0 text-right translate-x-16 translate-y-4 scale-95 transition-all duration-700 ease-out mb-6 flex justify-end">
                    <div class="max-w-2xl p-6 flex gap-6 rounded-lg transition-all duration-300 flex-row-reverse">
                        <div class="flex-shrink-0 w-20 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center self-stretch">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">Arquiteto de Software</h3>
                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                Perfeito para arquitetos que precisam projetar e comunicar estruturas de dados complexas para equipes de desenvolvimento, garantindo alinhamento e clareza.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Z-Layout: Card 3 - Embaixo Esquerdo (com offset para criar o Z) -->
                <div class="persona-card opacity-0 -translate-x-16 translate-y-4 scale-95 transition-all duration-700 ease-out mb-6 flex justify-start">
                    <div class="max-w-2xl p-6 flex gap-6 rounded-lg transition-all duration-300">
                        <div class="flex-shrink-0 w-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center self-stretch">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">DBA / Administrador</h3>
                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                Essencial para DBAs que precisam documentar, manter e otimizar estruturas de bancos de dados em produção, garantindo performance e organização.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Z-Layout: Card 4 - Embaixo Direito -->
                <div class="persona-card opacity-0 text-right translate-x-16 translate-y-4 scale-95 transition-all duration-700 ease-out flex justify-end">
                    <div class="max-w-2xl p-6 flex gap-6 rounded-lg transition-all duration-300 flex-row-reverse">
                        <div class="flex-shrink-0 w-20 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center self-stretch">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">Tech Lead</h3>
                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                Perfeito para líderes técnicos que precisam alinhar a equipe sobre a arquitetura de dados e facilitar a comunicação entre desenvolvedores.
                            </p>
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

    <!-- Pricing Section -->
    <section id="pricing" class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                    Planos e <span class="landing-primary-color">Preços</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Escolha o plano ideal para suas necessidades. Todos os planos incluem acesso completo às funcionalidades principais.
                </p>
            </div>

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

