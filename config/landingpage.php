<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Landing Page Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações estáticas para a landing page do CraftDB
    |
    */

    'site' => [
        'name' => 'CraftDB',
        'tagline' => 'Crie diagramas de banco de dados com arte e precisão',
        'description' => 'CraftDB é uma aplicação web moderna para criação, visualização e gerenciamento de diagramas de banco de dados. Crie diagramas ER de forma visual e interativa.',
        'email' => 'contato@craftdb.com',
        'phone' => null,
    ],

    'social' => [
        'twitter' => '#',
        'github' => '#',
        'linkedin' => '#',
    ],

    'features' => [
        [
            'icon' => 'palette',
            'title' => 'Criação Visual de Diagramas',
            'description' => 'Interface intuitiva para criar diagramas de banco de dados de forma visual e interativa.',
            'details' => 'Não precisa escrever código, apenas arraste e conecte.',
        ],
        [
            'icon' => 'table',
            'title' => 'Gerenciamento de Tabelas',
            'description' => 'Crie e edite tabelas com suas respectivas colunas de forma simples e eficiente.',
            'details' => 'Defina tipos de dados, chaves primárias e estrangeiras.',
        ],
        [
            'icon' => 'project-diagram',
            'title' => 'Relacionamentos',
            'description' => 'Defina relacionamentos entre tabelas (1:1, 1:N, N:1, N:N) com visualização gráfica.',
            'details' => 'Visualização gráfica clara e intuitiva.',
        ],
        [
            'icon' => 'chart-network',
            'title' => 'Visualização Gráfica',
            'description' => 'Visualize seus diagramas com gráficos de força interativos e navegação intuitiva.',
            'details' => 'Gráficos de força interativos (force-directed graphs) com zoom, pan e navegação intuitiva.',
        ],
        [
            'icon' => 'users',
            'title' => 'Sistema de Equipes',
            'description' => 'Colabore em equipes com controle de acesso baseado em roles e permissões granulares.',
            'details' => 'Convite membros e trabalhe juntos em projetos.',
        ],
        [
            'icon' => 'heart',
            'title' => 'Favoritos',
            'description' => 'Marque seus diagramas favoritos para acesso rápido.',
            'details' => 'Organize seus projetos mais importantes de forma eficiente.',
        ],
        [
            'icon' => 'upload',
            'title' => 'Importação',
            'description' => 'Importe estruturas de banco de dados existentes.',
            'details' => 'Suporte para múltiplos formatos e integração com ferramentas populares.',
        ],
        [
            'icon' => 'download',
            'title' => 'Exportação',
            'description' => 'Exporte seus diagramas em diferentes formatos.',
            'details' => 'Compartilhe com sua equipe ou use em documentação.',
        ],
        [
            'icon' => 'lock',
            'title' => 'Autenticação Segura',
            'description' => 'Autenticação completa com Laravel Fortify, autenticação de dois fatores (2FA) e recuperação de senha segura.',
            'details' => 'Segurança em primeiro lugar.',
        ],
    ],

    'pricing' => [
        'plans' => [
            [
                'name' => 'Free',
                'slug' => 'free',
                'price' => 0,
                'currency' => 'R$',
                'period' => 'mês',
                'description' => 'Perfeito para começar',
                'popular' => false,
                'features' => [
                    'Até 5 diagramas',
                    '1 equipe',
                    'Até 3 membros por equipe',
                    'Importação básica',
                    'Exportação em PNG',
                ],
                'cta_text' => 'Começar Grátis',
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 49,
                'currency' => 'R$',
                'period' => 'mês',
                'description' => 'Para equipes profissionais',
                'popular' => true,
                'features' => [
                    'Diagramas ilimitados',
                    'Equipes ilimitadas',
                    'Membros ilimitados',
                    'Importação avançada',
                    'Exportação em múltiplos formatos',
                    'Suporte prioritário',
                    'API access',
                ],
                'cta_text' => 'Começar Agora',
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price' => null,
                'price_text' => 'Custom',
                'currency' => null,
                'period' => null,
                'description' => 'Para grandes organizações',
                'popular' => false,
                'features' => [
                    'Tudo do plano Pro',
                    'SSO e autenticação customizada',
                    'Suporte dedicado 24/7',
                    'On-premise deployment',
                    'Treinamento personalizado',
                    'SLA garantido',
                ],
                'cta_text' => 'Fale Conosco',
                'cta_link' => 'mailto:contato@craftdb.com',
            ],
        ],
    ],

    'faq' => [
        [
            'question' => 'Posso mudar de plano depois?',
            'answer' => 'Sim! Você pode atualizar ou fazer downgrade do seu plano a qualquer momento.',
        ],
        [
            'question' => 'Há limite de uso no plano gratuito?',
            'answer' => 'O plano gratuito permite até 5 diagramas e 1 equipe com até 3 membros.',
        ],
        [
            'question' => 'Quais métodos de pagamento são aceitos?',
            'answer' => 'Aceitamos cartão de crédito, débito e PIX para clientes brasileiros.',
        ],
        [
            'question' => 'Posso cancelar minha assinatura a qualquer momento?',
            'answer' => 'Sim, você pode cancelar sua assinatura a qualquer momento sem taxas de cancelamento.',
        ],
        [
            'question' => 'Há suporte técnico disponível?',
            'answer' => 'Sim! Oferecemos suporte por email para todos os planos, com suporte prioritário para planos Pro e Enterprise.',
        ],
    ],

    'how_it_works' => [
        [
            'step' => 1,
            'title' => 'Crie seu Diagrama',
            'description' => 'Comece criando um novo diagrama e dê um nome para seu projeto.',
        ],
        [
            'step' => 2,
            'title' => 'Adicione Tabelas',
            'description' => 'Crie tabelas, defina colunas e configure chaves primárias e estrangeiras.',
        ],
        [
            'step' => 3,
            'title' => 'Conecte e Visualize',
            'description' => 'Defina relacionamentos e visualize seu diagrama em um gráfico interativo.',
        ],
    ],

    'testimonials' => [
        // Pode ser adicionado no futuro
    ],

    'footer' => [
        'links' => [
            'product' => [
                'title' => 'Produto',
                'items' => [
                    ['label' => 'Features', 'route' => 'landing.features'],
                    ['label' => 'Pricing', 'route' => 'landing.pricing'],
                    ['label' => 'Sobre', 'route' => 'landing.about'],
                ],
            ],
            'resources' => [
                'title' => 'Recursos',
                'items' => [
                    ['label' => 'Documentação', 'url' => '#'],
                    ['label' => 'API', 'url' => '#'],
                    ['label' => 'Suporte', 'url' => '#'],
                ],
            ],
        ],
    ],
];



