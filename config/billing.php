<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription plans (Stripe Price IDs)
    |--------------------------------------------------------------------------
    |
    | Define your plans here. Create products and prices in the Stripe
    | Dashboard, then add the price IDs below. Use env() for sensitive IDs.
    |
    */  

    'plans' => [
        'free' => [
            'name' => 'Free',
            'emoji' => '🌱',
            'description' => 'Para projetos pessoais e aprendizado.',
            'price_id' => null,
            'price' => 0,
            'interval' => null,
            'features' => [
                '3 diagramas',
                'Uso individual',
                'Links públicos de diagrama',
                'Suporte por email',
            ],
        ],

        'pro_usd' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'Para devs e freelancers que precisam de mais.',
            'price_id' => env('STRIPE_PRICE_PRO_USD', 'price_pro_usd'),
            'price' => 9.99,
            'interval' => 'month',
            'features' => [
                'Diagramas ilimitados',
                'Tabelas ilimitadas por diagrama',
                'Import SQL / Laravel Migrations',
                'Links públicos de diagrama',
                'Áreas, Sticky Notes, Graph View',
                'Uso individual (sem times)',
                'Suporte prioritário',
            ],
        ],

        'pro_brl' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'Para devs e freelancers que precisam de mais.',
            'price_id' => env('STRIPE_PRICE_PRO_BRL', 'price_pro_brl'),
            'price' => 29.99,
            'interval' => 'month',
            'features' => [
                'Diagramas ilimitados',
                'Tabelas ilimitadas por diagrama',
                'Import SQL / Laravel Migrations',
                'Links públicos de diagrama',
                'Áreas, Sticky Notes, Graph View',
                'Uso individual (sem times)',
                'Suporte prioritário',
            ],
        ],

        'pro_usd_annual' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'Para devs e freelancers que precisam de mais.',
            'price_id' => env('STRIPE_PRICE_PRO_USD_ANNUAL', 'price_pro_usd_annual'),
            'price' => 99.99,
            'interval' => 'year',
            'features' => [
                'Diagramas ilimitados',
                'Tabelas ilimitadas por diagrama',
                'Import SQL / Laravel Migrations',
                'Links públicos de diagrama',
                'Áreas, Sticky Notes, Graph View',
                'Uso individual (sem times)',
                'Suporte prioritário',
            ],
        ],

        'pro_brl_annual' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'Para devs e freelancers que precisam de mais.',
            'price_id' => env('STRIPE_PRICE_PRO_BRL_ANNUAL', 'price_pro_brl_annual'),
            'price' => 299.99,
            'interval' => 'year',
            'features' => [
                'Diagramas ilimitados',
                'Tabelas ilimitadas por diagrama',
                'Import SQL / Laravel Migrations',
                'Links públicos de diagrama',
                'Áreas, Sticky Notes, Graph View',
                'Uso individual (sem times)',
                'Suporte prioritário',
            ],
        ],

        'team_usd' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'Para times que trabalham juntos em schemas.',
            'price_id' => env('STRIPE_PRICE_TEAM_USD', 'price_team_usd'),
            'price' => 24.99,
            'interval' => 'month',
            'features' => [
                'Tudo do Pro',
                'Até 25 usuários no time',
                'Múltiplos times',
                'Permissões por membro (leitor, editor, dono)',
                'Histórico de visualizações',
                'Suporte dedicado',
            ],
        ],

        'team_brl' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'Para times que trabalham juntos em schemas.',
            'price_id' => env('STRIPE_PRICE_TEAM_BRL', 'price_team_brl'),
            'price' => 59.99,
            'interval' => 'month',
            'features' => [
                'Tudo do Pro',
                'Até 25 usuários no time',
                'Múltiplos times',
                'Permissões por membro (leitor, editor, dono)',
                'Histórico de visualizações',
                'Suporte dedicado',
            ],
        ],

        'team_usd_annual' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'Para times que trabalham juntos em schemas.',
            'price_id' => env('STRIPE_PRICE_TEAM_USD_ANNUAL', 'price_team_usd_annual'),
            'price' => 219.99,
            'interval' => 'year',
            'features' => [
                'Tudo do Pro',
                'Até 25 usuários no time',
                'Múltiplos times',
                'Permissões por membro (leitor, editor, dono)',
                'Histórico de visualizações',
                'Suporte dedicado',
            ],
        ],

        'team_brl_annual' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'Para times que trabalham juntos em schemas.',
            'price_id' => env('STRIPE_PRICE_TEAM_BRL_ANNUAL', 'price_team_brl_annual'),
            'price' => 599.99,
            'interval' => 'year',
            'features' => [
                'Tudo do Pro',
                'Até 25 usuários no time',
                'Múltiplos times',
                'Permissões por membro (leitor, editor, dono)',
                'Histórico de visualizações',
                'Suporte dedicado',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription name (Cashier)
    |--------------------------------------------------------------------------
    */
    'subscription_name' => 'default',

];
