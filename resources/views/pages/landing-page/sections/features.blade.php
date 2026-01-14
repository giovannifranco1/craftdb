<section id="features" class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-6">
                Modelagem visual, <span class="bg-gradient-to-r from-blue-600 via-cyan-500 to-blue-600 bg-clip-text text-transparent">colaboração</span> e produtividade
            </h2>
            <p class="text-lg text-gray-600">
                Tudo que você precisa para projetar bancos de dados profissionais
                de forma visual e colaborativa.
            </p>
        </div>

        <!-- Features Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $features = [
                    [
                        'icon' => 'table',
                        'title' => 'Tabelas visuais',
                        'description' => 'Crie tabelas com campos, tipos e constraints de forma visual e intuitiva.',
                    ],
                    [
                        'icon' => 'link',
                        'title' => 'Relacionamentos FK',
                        'description' => 'Visualize e crie foreign keys arrastando conexões entre tabelas.',
                    ],
                    [
                        'icon' => 'note',
                        'title' => 'Sticky Notes',
                        'description' => 'Adicione notas e documentação diretamente no seu diagrama.',
                    ],
                    [
                        'icon' => 'palette',
                        'title' => 'Múltiplos temas',
                        'description' => 'Dark mode, light mode e temas coloridos totalmente personalizáveis.',
                    ],
                    [
                        'icon' => 'folder',
                        'title' => 'Workspaces',
                        'description' => 'Organize seus projetos em workspaces colaborativos e estruturados.',
                    ],
                    [
                        'icon' => 'git',
                        'title' => 'Versionamento',
                        'description' => 'Controle de versões dos seus diagramas com histórico completo.',
                    ],
                    [
                        'icon' => 'users',
                        'title' => 'Colaboração em tempo real',
                        'description' => 'Trabalhe simultaneamente com sua equipe no mesmo diagrama.',
                    ],
                    [
                        'icon' => 'code',
                        'title' => 'Exportação SQL',
                        'description' => 'Exporte seus diagramas para scripts SQL prontos para executar.',
                    ],
                    [
                        'icon' => 'cloud',
                        'title' => 'Acesso na nuvem',
                        'description' => 'Acesse seus diagramas de qualquer lugar, em qualquer dispositivo.',
                    ],
                ];
            @endphp

            @foreach($features as $index => $feature)
                <div class="glass-card p-6 glow-effect group hover:border-blue-600/30 transition-all duration-300">
                    <div class="feature-icon mb-4 group-hover:scale-110 transition-transform duration-300">
                        @if($feature['icon'] === 'table')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'link')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        @elseif($feature['icon'] === 'note')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'palette')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                            </svg>
                        @elseif($feature['icon'] === 'folder')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'git')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        @elseif($feature['icon'] === 'users')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        @elseif($feature['icon'] === 'code')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        @elseif($feature['icon'] === 'cloud')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $feature['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
