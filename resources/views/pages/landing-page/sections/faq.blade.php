<section id="faq" class="py-24 relative scroll-animate">
    <div class="container mx-auto px-6">
        <!-- Section Header -->
        <div class="max-w-3xl mx-auto text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                Perguntas
                <span class="gradient-text">frequentes</span>
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
                        'answer' => 'A exportação para SQL está em desenvolvimento. Hoje você pode importar schemas a partir de arquivos SQL (MySQL) e de arquivos de Laravel Migration.',
                    ],
                    [
                        'question' => 'Quais bancos e tecnologias são suportados?',
                        'answer' => 'Para importação: SQL (MySQL) e arquivos de Laravel Migration. O CraftDB suporta modelagem visual para MySQL, PostgreSQL e SQLite. Suporte a mais formatos está em expansão.',
                    ],
                    [
                        'question' => 'Como funciona o trabalho em times?',
                        'answer' => 'Você cria um time, convida membros por email e define o papel de cada um (editor ou viewer). Os diagramas ficam organizados por time e você pode compartilhar o link público para quem não tem conta.',
                    ],
                    [
                        'question' => 'Posso importar schemas existentes?',
                        'answer' => 'Sim. Você pode importar a partir de arquivos SQL (MySQL) ou de arquivos de Laravel Migration (.php). O CraftDB detecta tabelas, campos e relacionamentos automaticamente.',
                    ],
                    [
                        'question' => 'Os diagramas são salvos na nuvem?',
                        'answer' => 'Sim, todos os diagramas são salvos na nuvem. Você pode acessar de qualquer dispositivo com sua conta.',
                    ],
                    [
                        'question' => 'Existe limite de tabelas por diagrama?',
                        'answer' => 'Sim. Atualmente há um limite de 30 tabelas por diagrama. Isso garante boa performance no canvas.',
                    ],
                    [
                        'question' => 'Posso usar offline?',
                        'answer' => 'Atualmente o CraftDB funciona apenas online. Os dados ficam na nuvem e você precisa de internet para acessar e editar.',
                    ],
                    [
                        'question' => 'Preciso de cartão de crédito para começar?',
                        'answer' => 'Não. Você pode criar uma conta e usar o plano gratuito sem informar cartão de crédito.',
                    ],
                ];
            @endphp

            @foreach($faqs as $index => $faq)
                <div class="glass-card overflow-hidden">
                    <button
                        @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                        class="w-full px-6 py-4 flex items-center justify-between gap-3 text-left"
                    >
                        <span class="font-medium text-gray-900">{{ $faq['question'] }}</span>
                        <span class="inline-flex shrink-0 transition-transform duration-300" x-bind:class="{ 'rotate-180': openIndex === {{ $index }} }">
                            <x-lucide-chevron-down class="w-5 h-5 text-gray-600" />
                        </span>
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
