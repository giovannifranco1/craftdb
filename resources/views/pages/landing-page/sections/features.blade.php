<section id="features" class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Modelagem visual,
                <span class="gradient-text">colaboração</span>
                e produtividade
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
                        'icon' => 'add-table',
                        'title' => 'Tabelas visuais',
                        'description' => 'Crie tabelas com campos, tipos e constraints de forma visual e intuitiva no canvas.',
                    ],
                    [
                        'icon' => 'graph',
                        'title' => 'Relacionamentos FK',
                        'description' => 'Visualize e crie foreign keys conectando tabelas no diagrama.',
                    ],
                    [
                        'icon' => 'add-area',
                        'title' => 'Grupos de tabelas',
                        'description' => 'Organize tabelas em grupos coloridos, redimensionáveis e nomeáveis.',
                    ],
                    [
                        'icon' => 'add-note',
                        'title' => 'Notas (Sticky Notes)',
                        'description' => 'Adicione notas e documentação diretamente no seu diagrama.',
                    ],
                    [
                        'icon' => 'palette',
                        'title' => 'Temas e aparência',
                        'description' => 'Dark mode, light mode e estilos de diagrama (colorido ou neutro).',
                    ],
                    [
                        'icon' => 'users',
                        'title' => 'Times e membros',
                        'description' => 'Organize diagramas em times, convide membros e defina papéis (editor/viewer).',
                    ],
                    [
                        'icon' => 'code',
                        'title' => 'Importação SQL e Laravel',
                        'description' => 'Importe schemas a partir de SQL (MySQL) ou arquivos de Laravel Migration.',
                    ],
                    [
                        'icon' => 'share',
                        'title' => 'Link público',
                        'description' => 'Compartilhe diagramas por link público, sem exigir login para visualizar.',
                    ],
                    [
                        'icon' => 'heart',
                        'title' => 'Favoritos',
                        'description' => 'Marque diagramas como favoritos e acesse rápido pelo dashboard.',
                    ],
                ];
            @endphp

            @foreach($features as $index => $feature)
                <div class="glass-card p-6 group hover:border-gray-900/30 transition-all duration-300">
                    <div class="feature-icon mb-4 group-hover:scale-110 transition-transform duration-300">
                        @if($feature['icon'] === 'add-table')
                            <flux:icon.add-table class="w-5 h-5" />
                        @elseif($feature['icon'] === 'graph')
                            <flux:icon.graph class="w-5 h-5" />
                        @elseif($feature['icon'] === 'add-area')
                            <flux:icon.add-area class="w-5 h-5" />
                        @elseif($feature['icon'] === 'add-note')
                            <flux:icon.add-note class="w-5 h-5" />
                        @elseif($feature['icon'] === 'palette')
                            <x-lucide-palette class="w-5 h-5" />
                        @elseif($feature['icon'] === 'users')
                            <x-lucide-users class="w-5 h-5" />
                        @elseif($feature['icon'] === 'code')
                            <x-lucide-file-code class="w-5 h-5" />
                        @elseif($feature['icon'] === 'share')
                            <x-lucide-share-2 class="w-5 h-5" />
                        @elseif($feature['icon'] === 'heart')
                            <x-lucide-heart class="w-5 h-5" />
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
