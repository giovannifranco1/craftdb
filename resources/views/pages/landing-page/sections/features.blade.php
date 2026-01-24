<section id="features" class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Modelagem visual,{" "}
                <span class="gradient-text">colaboração</span>
                {" "}e produtividade
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
                <div class="glass-card p-6 glow-effect group hover:border-gray-900/30 transition-all duration-300">
                    <div class="feature-icon mb-4 group-hover:scale-110 transition-transform duration-300">
                        @if($feature['icon'] === 'table')
                            <x-lucide-table class="w-5 h-5" />
                        @elseif($feature['icon'] === 'link')
                            <x-lucide-link-2 class="w-5 h-5" />
                        @elseif($feature['icon'] === 'note')
                            <x-lucide-sticky-note class="w-5 h-5" />
                        @elseif($feature['icon'] === 'palette')
                            <x-lucide-palette class="w-5 h-5" />
                        @elseif($feature['icon'] === 'folder')
                            <x-lucide-folder-kanban class="w-5 h-5" />
                        @elseif($feature['icon'] === 'git')
                            <x-lucide-git-branch class="w-5 h-5" />
                        @elseif($feature['icon'] === 'users')
                            <x-lucide-users class="w-5 h-5" />
                        @elseif($feature['icon'] === 'code')
                            <x-lucide-file-code class="w-5 h-5" />
                        @elseif($feature['icon'] === 'cloud')
                            <x-lucide-cloud class="w-5 h-5" />
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
