<section id="faq" class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-6">
                Perguntas <span class="bg-gradient-to-r from-blue-600 via-cyan-500 to-blue-600 bg-clip-text text-transparent">frequentes</span>
            </h2>
            <p class="text-lg text-gray-600">
                Tire suas dúvidas sobre o CraftDB.
            </p>
        </div>

        <!-- FAQ List -->
        <div class="max-w-3xl mx-auto space-y-4" x-data="{ openIndex: 0 }">
            @php
                $faqs = [
                    [
                        'question' => 'Posso exportar meus diagramas para SQL?',
                        'answer' => 'Sim! O CraftDB suporta exportação para PostgreSQL, MySQL, SQLite e SQL Server. Você pode gerar scripts completos com CREATE TABLE, constraints, índices e foreign keys.',
                    ],
                    [
                        'question' => 'Quais bancos de dados são suportados?',
                        'answer' => 'Suportamos os principais bancos relacionais: PostgreSQL, MySQL, MariaDB, SQLite, SQL Server e Oracle. Cada exportação respeita a sintaxe específica do banco escolhido.',
                    ],
                    [
                        'question' => 'Como funciona a colaboração em tempo real?',
                        'answer' => 'Basta compartilhar o link do seu diagrama ou convidar colaboradores por email. Todos veem as alterações instantaneamente, com cursores coloridos mostrando onde cada pessoa está trabalhando.',
                    ],
                    [
                        'question' => 'Posso importar schemas existentes?',
                        'answer' => 'Sim! Você pode importar schemas de arquivos SQL, conexão direta com banco de dados, ou arquivos de migração. O CraftDB detecta automaticamente tabelas, campos e relacionamentos.',
                    ],
                    [
                        'question' => 'Os diagramas são salvos na nuvem?',
                        'answer' => 'Sim, todos os diagramas são salvos automaticamente na nuvem com versionamento. Você pode acessar de qualquer dispositivo e nunca vai perder seu trabalho.',
                    ],
                    [
                        'question' => 'Existe limite de tabelas por diagrama?',
                        'answer' => 'No plano gratuito você pode ter até 20 tabelas por diagrama. Nos planos pagos, não há limite - já temos usuários com diagramas de 200+ tabelas rodando perfeitamente.',
                    ],
                    [
                        'question' => 'Posso usar offline?',
                        'answer' => 'Atualmente o CraftDB funciona apenas online para garantir sincronização em tempo real. Estamos desenvolvendo um modo offline que será lançado em breve.',
                    ],
                    [
                        'question' => 'Como funciona o período de teste?',
                        'answer' => 'Você pode usar o plano gratuito para sempre, sem necessidade de cartão de crédito. Para testar os recursos Pro, oferecemos 14 dias grátis com todos os recursos liberados.',
                    ],
                ];
            @endphp

            @foreach($faqs as $index => $faq)
                <div class="glass-card overflow-hidden">
                    <button
                        @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                        class="w-full px-6 py-4 flex items-center justify-between text-left"
                    >
                        <span class="font-medium text-gray-900">{{ $faq['question'] }}</span>
                        <svg
                            class="w-5 h-5 text-gray-600 transition-transform duration-300"
                            :class="{ 'rotate-180': openIndex === {{ $index }} }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div
                        x-show="openIndex === {{ $index }}"
                        class="px-6 pb-4"
                    >
                        <p class="text-gray-600 leading-relaxed">
                            {{ $faq['answer'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
