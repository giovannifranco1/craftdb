<section id="how-it-works" class="py-24 relative section-gradient scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Da ideia ao diagrama em <span class="bg-gradient-to-r from-blue-600 via-cyan-500 to-blue-600 bg-clip-text text-transparent">minutos</span>
            </h2>
            <p class="text-lg text-gray-600">
                Simples assim. Crie, conecte e compartilhe seus schemas de banco de dados.
            </p>
        </div>

        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Steps -->
            <div class="space-y-8">
                @php
                    $steps = [
                        [
                            'icon' => 'folder',
                            'title' => 'Crie seu workspace',
                            'description' => 'Organize seus projetos em workspaces para melhor estrutura.',
                        ],
                        [
                            'icon' => 'table',
                            'title' => 'Adicione tabelas',
                            'description' => 'Arraste e defina campos, tipos e constraints visualmente.',
                        ],
                        [
                            'icon' => 'link',
                            'title' => 'Conecte relacionamentos',
                            'description' => 'Crie FKs arrastando conexões entre as tabelas.',
                        ],
                        [
                            'icon' => 'share',
                            'title' => 'Exporte ou compartilhe',
                            'description' => 'Gere SQL, exporte PNG ou compartilhe com sua equipe.',
                        ],
                    ];
                @endphp

                @foreach($steps as $index => $step)
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-blue-600/10 border border-blue-600/20 flex items-center justify-center">
                                @if($step['icon'] === 'folder')
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                    </svg>
                                @elseif($step['icon'] === 'table')
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                @elseif($step['icon'] === 'link')
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                    </svg>
                                @elseif($step['icon'] === 'share')
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-medium text-blue-600">Passo {{ $index + 1 }}</span>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                {{ $step['title'] }}
                            </h3>
                            <p class="text-gray-600 text-sm">
                                {{ $step['description'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Visual representation -->
            <div class="glass-card p-6 glow-effect">
                <div class="space-y-4">
                    <!-- Workspace selector -->
                    <div class="flex items-center gap-3 p-3 bg-white/50 rounded-lg border border-gray-200/30">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                        </svg>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">Workspace</div>
                            <div class="text-xs text-gray-600">Projeto E-commerce</div>
                        </div>
                        <div class="text-xs text-blue-600">3 diagramas</div>
                    </div>

                    <!-- Table being created -->
                    <div class="p-4 bg-white/50 rounded-lg border border-gray-200/30">
                        <div class="text-xs text-gray-600 mb-3">Nova tabela detectada</div>
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-900">products</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-500/20 text-green-600">criando...</span>
                        </div>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2 p-2 bg-white/50 rounded">
                                <span class="text-blue-600">🔑</span>
                                <span class="text-gray-900">id</span>
                                <span class="text-gray-600 ml-auto">uuid PRIMARY KEY</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 bg-white/50 rounded">
                                <span class="text-gray-400">○</span>
                                <span class="text-gray-900">name</span>
                                <span class="text-gray-600 ml-auto">varchar(255)</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 bg-white/50 rounded">
                                <span class="text-gray-400">○</span>
                                <span class="text-gray-900">price</span>
                                <span class="text-gray-600 ml-auto">decimal(10,2)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex gap-3">
                        <button class="flex-1 inline-flex items-center justify-center px-4 py-2.5 landing-btn-primary text-white text-sm font-medium rounded-lg transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Adicionar campo
                        </button>
                        <button class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border-2 border-gray-300 rounded-lg text-sm font-medium landing-btn-outline transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            Criar FK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
