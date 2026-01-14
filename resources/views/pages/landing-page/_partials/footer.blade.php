<footer class="relative border-t border-gray-200/30">
    <!-- CTA Section -->
    <div class="container mx-auto px-6 py-16">
        <div class="glass-card p-8 md:p-12 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                Pronto para modelar seu banco de dados?
            </h2>
            <p class="text-gray-600 mb-8 max-w-xl mx-auto">
                Comece grátis hoje mesmo. Sem cartão de crédito, sem complicação.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 landing-btn-primary font-bold text-white text-sm rounded-lg transition shadow-sm">
                        Ir para Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-block px-6 py-3 landing-btn-primary font-bold text-white text-sm rounded-lg transition shadow-sm">
                        Começar grátis
                    </a>
                    <a href="#" class="inline-block px-6 py-3 border-1 border-gray-300/50 rounded-lg font-medium text-sm landing-btn-outline transition">
                        Falar com vendas
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Links Section -->
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
            <!-- Brand -->
            <div class="col-span-2 md:col-span-1">
                <a href="/" class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-gray-900">CraftDB</span>
                </a>
                <p class="text-sm text-gray-600 mb-4">
                    A forma mais visual de projetar bancos de dados.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-8 h-8 rounded-lg bg-gray-100/50 border border-gray-200/30 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:border-blue-600/30 transition-colors" aria-label="Twitter">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                        </svg>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-gray-100/50 border border-gray-200/30 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:border-blue-600/30 transition-colors" aria-label="GitHub">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"></path>
                        </svg>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-gray-100/50 border border-gray-200/30 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:border-blue-600/30 transition-colors" aria-label="LinkedIn">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Produto -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Produto</h4>
                <ul class="space-y-2">
                    <li><a href="#features" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Recursos</a></li>
                    <li><a href="#pricing" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Preços</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Changelog</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Roadmap</a></li>
                </ul>
            </div>

            <!-- Recursos -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Recursos</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Documentação</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Tutoriais</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Blog</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">API Reference</a></li>
                </ul>
            </div>

            <!-- Empresa -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Empresa</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Sobre</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Carreiras</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Contato</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Parceiros</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Legal</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Privacidade</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Termos</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Cookies</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">Licenças</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="border-t border-gray-200/30">
        <div class="container mx-auto px-6 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-600">
                    © {{ date('Y') }} CraftDB. Todos os direitos reservados.
                </p>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-sm text-gray-600">Todos os sistemas operacionais</span>
                </div>
            </div>
        </div>
    </div>
</footer>
