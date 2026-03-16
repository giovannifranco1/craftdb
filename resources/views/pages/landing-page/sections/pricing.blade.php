<section id="pricing" class="py-24 relative section-gradient scroll-animate">
    <div class="container mx-auto px-6" x-data="{ annual: false }">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-10">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Preços simples,
                <span class="gradient-text">sem surpresas</span>
            </h2>
            <p class="text-lg text-gray-600">
                Comece grátis e escale conforme sua necessidade.
                Sem cartão de crédito para começar.
            </p>
        </div>

        <!-- Billing Toggle -->
        <div class="flex items-center justify-center gap-4 mb-12">
            <span class="text-sm font-medium" :class="annual ? 'text-gray-400' : 'text-gray-900'">Mensal</span>

            <button
                @click="annual = !annual"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                :class="annual ? 'bg-gray-900' : 'bg-gray-300'"
            >
                <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="annual ? 'translate-x-6' : 'translate-x-1'"
                ></span>
            </button>

            <span class="text-sm font-medium" :class="annual ? 'text-gray-900' : 'text-gray-400'">
                Anual
                <span class="ml-1.5 inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Economize ~17%</span>
            </span>
        </div>

        <!-- Pricing Grid -->
        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">

            <!-- Hobby -->
            <div class="glass-card p-8 relative">
                <div class="text-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Hobby</h3>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-4xl font-bold text-gray-900">Grátis</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Para projetos pessoais e aprendizado.</p>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach(['3 diagramas', 'Uso individual', 'Links públicos de diagrama', 'Suporte por email'] as $feature)
                        <li class="flex items-center gap-3">
                            <x-lucide-check class="w-5 h-5 text-gray-900 flex-shrink-0" />
                            <span class="text-sm text-gray-600">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full block text-center btn-secondary">Começar grátis</a>
                @else
                    <a href="{{ route('register') }}" class="w-full block text-center btn-secondary">Começar grátis</a>
                @endauth
            </div>

            <!-- Pro -->
            <div class="glass-card p-8 relative border-gray-900/50">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="px-3 py-1 rounded-full bg-gray-900 text-white text-xs font-medium">Mais popular</span>
                </div>
                <div class="text-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Pro</h3>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-4xl font-bold text-gray-900" x-text="annual ? 'R$299,99' : 'R$29,99'">R$29,99</span>
                        <span class="text-gray-600" x-text="annual ? '/ano' : '/mês'">/mês</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" x-show="annual">equivale a R$25/mês</p>
                    <p class="text-sm text-gray-600 mt-2">Para devs e freelancers que precisam de mais.</p>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach(['Diagramas ilimitados', 'Tabelas ilimitadas por diagrama', 'Import SQL / Laravel Migrations', 'Links públicos de diagrama', 'Áreas, Sticky Notes, Graph View', 'Uso individual (sem times)', 'Suporte prioritário'] as $feature)
                        <li class="flex items-center gap-3">
                            <x-lucide-check class="w-5 h-5 text-gray-900 flex-shrink-0" />
                            <span class="text-sm text-gray-600">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full block text-center btn-primary">Assinar Pro</a>
                @else
                    <a href="{{ route('register') }}" class="w-full block text-center btn-primary">Assinar Pro</a>
                @endauth
            </div>

            <!-- Team -->
            <div class="glass-card p-8 relative">
                <div class="text-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Team</h3>
                    <div class="flex items-baseline justify-center gap-1">
                        <span class="text-4xl font-bold text-gray-900" x-text="annual ? 'R$599,99' : 'R$59,99'">R$59,99</span>
                        <span class="text-gray-600" x-text="annual ? '/ano' : '/mês'">/mês</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" x-show="annual">equivale a R$50/mês</p>
                    <p class="text-sm text-gray-600 mt-2">Para times que trabalham juntos em schemas.</p>
                </div>
                <ul class="space-y-3 mb-8">
                    @foreach(['Tudo do Pro', 'Até 25 usuários no time', 'Múltiplos times', 'Permissões por membro (leitor, editor, dono)', 'Histórico de visualizações', 'Suporte dedicado'] as $feature)
                        <li class="flex items-center gap-3">
                            <x-lucide-check class="w-5 h-5 text-gray-900 flex-shrink-0" />
                            <span class="text-sm text-gray-600">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full block text-center btn-secondary">Assinar Team</a>
                @else
                    <a href="{{ route('register') }}" class="w-full block text-center btn-secondary">Assinar Team</a>
                @endauth
            </div>

        </div>
    </div>
</section>
