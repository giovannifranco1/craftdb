<section class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Amado por{" "}
                <span class="gradient-text">desenvolvedores</span>
            </h2>
            <p class="text-lg text-gray-600">
                Veja o que profissionais de tecnologia estão dizendo sobre o CraftDB.
            </p>
        </div>

        <!-- Testimonials Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $testimonials = [
                    [
                        'quote' => 'A experiência visual é muito boa. Organizamos os diagramas em times e o link público facilita compartilhar com o cliente.',
                        'author' => 'Carlos Silva',
                        'role' => 'Tech Lead @ Fintech Brasil',
                        'avatar' => 'CS',
                        'rating' => 5,
                    ],
                    [
                        'quote' => 'As sticky notes são perfeitas para documentar decisões de arquitetura. Consigo explicar o "porquê" de cada decisão direto no diagrama.',
                        'author' => 'Marina Costa',
                        'role' => 'DBA @ E-commerce Plus',
                        'avatar' => 'MC',
                        'rating' => 5,
                    ],
                    [
                        'quote' => 'A importação de Laravel Migration economizou muito tempo. Já tinha as migrações e em poucos cliques o diagrama estava pronto.',
                        'author' => 'João Santos',
                        'role' => 'Backend Developer @ SaaS Startup',
                        'avatar' => 'JS',
                        'rating' => 5,
                    ],
                    [
                        'quote' => 'As áreas no canvas ajudam a separar domínios do modelo. Fica muito mais legível do que um monte de tabela solta.',
                        'author' => 'Ana Oliveira',
                        'role' => 'Arquiteta de Software @ Consultoria',
                        'avatar' => 'AO',
                        'rating' => 5,
                    ],
                    [
                        'quote' => 'A interface é intuitiva. Nossa equipe começou a usar e em pouco tempo todo mundo estava modelando sem precisar de treinamento.',
                        'author' => 'Pedro Lima',
                        'role' => 'CTO @ Agency Digital',
                        'avatar' => 'PL',
                        'rating' => 5,
                    ],
                    [
                        'quote' => 'O dark mode e os temas do diagrama fazem diferença quando você passa horas modelando. Menos cansaço visual.',
                        'author' => 'Fernanda Rocha',
                        'role' => 'Full Stack Developer @ Tech Corp',
                        'avatar' => 'FR',
                        'rating' => 5,
                    ],
                ];
            @endphp

            @foreach($testimonials as $index => $testimonial)
                <div class="glass-card p-6 glow-effect testimonial-card">
                    <!-- Star Rating -->
                    <div class="flex gap-0.5 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 {{ $i < $testimonial['rating'] ? 'text-amber-400 fill-amber-400' : 'text-gray-300/30' }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                            </svg>
                        @endfor
                    </div>

                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "{{ $testimonial['quote'] }}"
                    </p>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-900 via-gray-700 to-gray-900 flex items-center justify-center text-white font-medium text-sm ring-2 ring-gray-900/20 ring-offset-2 ring-offset-white">
                            {{ $testimonial['avatar'] }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 text-sm">
                                {{ $testimonial['author'] }}
                            </div>
                            <div class="text-xs text-gray-600">
                                {{ $testimonial['role'] }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
