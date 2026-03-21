<?php

return [
    'meta' => [
        'default_title' => 'CraftDB - A forma mais visual de projetar seu banco de dados',
        'default_description' => 'Crie diagramas de banco de dados intuitivos. Defina tabelas, relacionamentos e trabalhe em times. Zero complexidade, máxima produtividade.',
        'index_title' => 'CraftDB - A forma mais visual de projetar seu banco de dados',
        'features_title' => 'Recursos - CraftDB',
        'pricing_title' => 'Preços - CraftDB',
        'about_title' => 'Sobre - CraftDB',
    ],

    'nav' => [
        'features' => 'Recursos',
        'pricing' => 'Preços',
        'how_it_works' => 'Como funciona',
        'faq' => 'FAQ',
        'dashboard' => 'Dashboard',
        'sign_in' => 'Entrar',
    ],

    'brand' => [
        'beta' => 'Beta',
    ],

    'footer' => [
        'features' => 'Recursos',
        'pricing' => 'Preços',
        'terms' => 'Termos',
        'privacy' => 'Privacidade',
    ],

    'hero' => [
        'headline_prefix' => 'A forma mais visual de',
        'headline_gradient' => 'projetar seu banco de dados',
        'subheadline' => 'Crie diagramas de banco de dados intuitivos. Defina tabelas, relacionamentos e trabalhe em times. Zero complexidade, máxima produtividade.',
        'cta_dashboard' => 'Ir para Dashboard',
        'cta_register' => 'Começar grátis',
        'cta_secondary' => 'Ver funcionalidades',
        'tech_line' => 'MySQL, PostgreSQL, SQLite e Laravel Migration',
        'macbook_alt' => 'MacBook',
        'macbook_screen_alt' => 'MacBook Screen',
    ],

    'logo_strip' => [
        'title_prefix' => 'Suporte a diversos',
        'title_gradient' => 'bancos de dados',
        'subtitle' => 'Compatível com os principais bancos relacionais e tecnologias',
    ],

    'features' => [
        'title_line1' => 'Modelagem visual,',
        'title_gradient' => 'colaboração',
        'title_line2' => 'e produtividade',
        'subtitle' => 'Tudo que você precisa para projetar bancos de dados profissionais de forma visual e colaborativa.',
        'items' => [
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
        ],
    ],

    'how_it_works' => [
        'badge' => 'Simples e poderoso',
        'title_line1' => 'Da ideia ao diagrama',
        'title_line2' => 'em minutos',
        'subtitle' => 'Quatro passos simples para criar, conectar e compartilhar seus schemas de banco de dados de forma profissional.',
        'steps' => [
            [
                'icon' => 'users',
                'title' => 'Crie seu time',
                'description' => 'Monte seu time e gerencie permissões para colaboração eficiente.',
                'color' => 'from-blue-500 to-cyan-500',
            ],
            [
                'icon' => 'add-table',
                'title' => 'Crie o diagrama',
                'description' => 'Modele seu banco visualmente, criando tabelas, campos e relacionamentos em um canvas intuitivo.',
                'color' => 'from-violet-500 to-purple-500',
            ],
            [
                'icon' => 'code',
                'title' => 'Importe seu schema',
                'description' => 'Traga rapidamente sua estrutura existente com importação de SQL ou Laravel Migration.',
                'color' => 'from-yellow-500 to-orange-500',
            ],
            [
                'icon' => 'share',
                'title' => 'Compartilhe',
                'description' => 'Gere um link público e compartilhe o diagrama sem exigir login.',
                'color' => 'from-emerald-500 to-teal-500',
            ],
        ],
    ],

    'pricing' => [
        'title_line1' => 'Preços simples,',
        'title_gradient' => 'sem surpresas',
        'subtitle' => 'Comece grátis e escale conforme sua necessidade. Sem cartão de crédito para começar.',
        'billing_monthly' => 'Mensal',
        'billing_annual' => 'Anual',
        'save_badge' => 'Economize ~17%',
        'plans' => [
            'hobby' => [
                'name' => 'Hobby',
                'price' => 'Grátis',
                'description' => 'Para projetos pessoais e aprendizado.',
                'features' => [
                    '3 diagramas',
                    'Uso individual',
                    'Links públicos de diagrama',
                    'Suporte por email',
                ],
                'cta' => 'Começar grátis',
            ],
            'pro' => [
                'name' => 'Pro',
                'badge' => 'Mais popular',
                'price_monthly' => 'R$29,99',
                'price_annual' => 'R$299,99',
                'period_monthly' => '/mês',
                'period_annual' => '/ano',
                'annual_equivalent' => 'equivale a R$25/mês',
                'description' => 'Para devs e freelancers que precisam de mais.',
                'features' => [
                    'Diagramas ilimitados',
                    'Tabelas ilimitadas por diagrama',
                    'Import SQL / Laravel Migrations',
                    'Links públicos de diagrama',
                    'Áreas, Sticky Notes, Graph View',
                    'Uso individual (sem times)',
                    'Suporte prioritário',
                ],
                'cta' => 'Assinar Pro',
            ],
            'team' => [
                'name' => 'Team',
                'price_monthly' => 'R$59,99',
                'price_annual' => 'R$599,99',
                'period_monthly' => '/mês',
                'period_annual' => '/ano',
                'annual_equivalent' => 'equivale a R$50/mês',
                'description' => 'Para times que trabalham juntos em schemas.',
                'features' => [
                    'Tudo do Pro',
                    'Até 25 usuários no time',
                    'Múltiplos times',
                    'Permissões por membro (leitor, editor, dono)',
                    'Histórico de visualizações',
                    'Suporte dedicado',
                ],
                'cta' => 'Assinar Team',
            ],
        ],
    ],

    'faq' => [
        'title_line1' => 'Perguntas',
        'title_gradient' => 'frequentes',
        'subtitle' => 'Tire suas dúvidas sobre o CraftDB.',
        'items' => [
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
        ],
    ],

    'statistics' => [
        'items' => [
            ['icon' => 'users', 'value' => '5.000+', 'label' => 'Desenvolvedores ativos'],
            ['icon' => 'code', 'value' => '50.000+', 'label' => 'Diagramas criados'],
            ['icon' => 'clock', 'value' => '99,9%', 'label' => 'Uptime garantido'],
            ['icon' => 'star', 'value' => '4,9', 'label' => 'Avaliação média'],
        ],
    ],

    'testimonials' => [
        'title_line1' => 'Amado por',
        'title_gradient' => 'desenvolvedores',
        'subtitle' => 'Veja o que profissionais de tecnologia estão dizendo sobre o CraftDB.',
        'items' => [
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
        ],
    ],
];
