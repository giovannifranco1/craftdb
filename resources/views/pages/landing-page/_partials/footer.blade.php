<footer class="relative border-t border-gray-200/30">
    <!-- Links Section -->
    <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-8">
            <!-- Brand -->
            <div class="col-span-2 md:col-span-1">
                <a href="/" class="flex items-center gap-2 mb-4">
                    <x-app-logo-icon class="size-8 fill-current" />
                    <span class="text-lg font-semibold text-gray-900">CraftDB</span>
                </a>
                <p class="text-sm text-gray-600 mb-4">
                    A forma mais visual de projetar bancos de dados.
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-8 h-8 rounded-lg bg-gray-100/50 border border-gray-200/30 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-900/30 transition-colors" aria-label="Twitter">
                        <x-lucide-twitter class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-gray-100/50 border border-gray-200/30 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-900/30 transition-colors" aria-label="GitHub">
                        <x-lucide-github class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-gray-100/50 border border-gray-200/30 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-900/30 transition-colors" aria-label="LinkedIn">
                        <x-lucide-linkedin class="w-4 h-4" />
                    </a>
                </div>
            </div>

            <!-- Produto -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Produto</h4>
                <ul class="space-y-2">
                    <li><a href="#features" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Recursos</a></li>
                    <li><a href="#pricing" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Preços</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Changelog</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Roadmap</a></li>
                </ul>
            </div>

            <!-- Recursos -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Recursos</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Documentação</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Tutoriais</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Blog</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">API Reference</a></li>
                </ul>
            </div>

            <!-- Empresa -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Empresa</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Sobre</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Carreiras</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Contato</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Parceiros</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Legal</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Privacidade</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Termos</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Cookies</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Licenças</a></li>
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
                    <img src="{{ Vite::asset('resources/images/logo_blop_v2.png') }}" alt="CraftDB Logo" class="w-10 h-10">
                    <span class="text-sm text-gray-600">Todos os sistemas operacionais</span>
                </div>
            </div>
        </div>
    </div>
</footer>
