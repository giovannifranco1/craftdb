<x-layouts.landing-page title="Sobre - CraftDB">
    <!-- Hero Section -->
    <section class="pt-28 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                Sobre o <span class="landing-primary-color">CraftDB</span>
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Criando diagramas de banco de dados com arte e precisão
            </p>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg max-w-none">
                <div class="bg-white p-6 rounded-xl border border-gray-200 mb-6 landing-card-hover">
                    <h2 class="text-2xl font-bold mb-3 landing-primary-color">Nossa Missão</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        O <strong>CraftDB</strong> foi criado com a missão de tornar a criação e gerenciamento de diagramas de banco de dados uma experiência visual, intuitiva e colaborativa. Acreditamos que desenvolvedores e arquitetos de software merecem ferramentas que facilitem o design e documentação de estruturas de dados.
                    </p>
                    <p class="text-gray-700 leading-relaxed">
                        O nome "CraftDB" reflete nossa filosofia: combinar o artesanato cuidadoso ("craft") com a precisão técnica necessária para trabalhar com bancos de dados ("DB"). Cada diagrama criado no CraftDB é uma obra de arte técnica, cuidadosamente elaborada.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 mb-6 landing-card-hover">
                    <h2 class="text-2xl font-bold mb-3 landing-primary-color">Para Quem é o CraftDB?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        O CraftDB é uma ferramenta profissional desenvolvida para:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2 mb-4">
                        <li><strong>Desenvolvedores</strong> que precisam documentar estruturas de banco de dados</li>
                        <li><strong>Arquitetos de Software</strong> que projetam esquemas de banco de dados</li>
                        <li><strong>Equipes de Desenvolvimento</strong> que colaboram em projetos de banco de dados</li>
                        <li><strong>Estudantes</strong> que estão aprendendo sobre modelagem de dados</li>
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 mb-6 landing-card-hover">
                    <h2 class="text-2xl font-bold mb-3 landing-primary-color">Tecnologias Modernas</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        O CraftDB é construído com as tecnologias mais modernas do ecossistema web:
                    </p>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Backend</h3>
                            <ul class="list-disc list-inside text-gray-700 space-y-1">
                                <li>Laravel 12</li>
                                <li>PHP 8.2+</li>
                                <li>Livewire 3.6</li>
                                <li>Eloquent ORM</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Frontend</h3>
                            <ul class="list-disc list-inside text-gray-700 space-y-1">
                                <li>Alpine.js</li>
                                <li>D3.js</li>
                                <li>Tailwind CSS</li>
                                <li>Vite</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 card-hover">
                    <h2 class="text-2xl font-bold mb-3 landing-primary-color">Arquitetura</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        O CraftDB utiliza uma arquitetura <strong>MVC (Model-View-Controller)</strong> combinada com componentes reativos do Livewire, seguindo os padrões e convenções do Laravel. Isso garante:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 space-y-2">
                        <li>Código limpo e organizado</li>
                        <li>Manutenibilidade facilitada</li>
                        <li>Escalabilidade para crescer com suas necessidades</li>
                        <li>Performance otimizada</li>
                    </ul>
                </div>
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
                Junte-se à comunidade CraftDB e comece a criar diagramas profissionais hoje mesmo
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

