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
            'name' => 'Hobby',
            'description' => 'Para projetos pessoais e aprendizado.',
            'price_id' => null,
            'price' => 0,
            'interval' => null,
            'features' => [
                '3 diagramas',
                '1 time com até 2 membros',
                'Links públicos de diagrama',
                'Suporte por email',
            ],
        ],

        'pro_usd' => [
            'name' => 'Pro',
            'description' => 'Para devs e freelancers que precisam de mais.',
            'price_id' => env('STRIPE_PRICE_PRO_USD', 'price_pro_usd'),
            'price' => (float) env('BILLING_PRO_USD_PRICE', 9),
            'interval' => 'month',
            'features' => [
                'Diagramas ilimitados',
                'Tabelas ilimitadas por diagrama',
                'Import SQL / Laravel Migrations',
                'Links públicos de diagrama',
                'Áreas, Sticky Notes, Graph View',
                '1 time com até 3 membros',
                'Suporte prioritário',
            ],
        ],

        'pro_brl' => [
            'name' => 'Pro',
            'description' => 'Para devs e freelancers que precisam de mais.',
            'price_id' => env('STRIPE_PRICE_PRO_BRL', 'price_pro_brl'),
            'price' => (float) env('BILLING_PRO_BRL_PRICE', 49.90),
            'interval' => 'month',
            'features' => [
                'Diagramas ilimitados',
                'Tabelas ilimitadas por diagrama',
                'Import SQL / Laravel Migrations',
                'Links públicos de diagrama',
                'Áreas, Sticky Notes, Graph View',
                '1 time com até 3 membros',
                'Suporte prioritário',
            ],
        ],

        'team_usd' => [
            'name' => 'Team',
            'description' => 'Para times que trabalham juntos em schemas.',
            'price_id' => env('STRIPE_PRICE_TEAM_USD', 'price_team_usd'),
            'price' => (float) env('BILLING_TEAM_USD_PRICE', 24),
            'interval' => 'month',
            'features' => [
                'Tudo do Pro',
                'Membros ilimitados no time',
                'Múltiplos times',
                'Permissões por membro (leitor, editor, dono)',
                'Histórico de visualizações',
                'Suporte dedicado',
            ],
        ],

        'team_brl' => [
            'name' => 'Team',
            'description' => 'Para times que trabalham juntos em schemas.',
            'price_id' => env('STRIPE_PRICE_TEAM_BRL', 'price_team_brl'),
            'price' => (float) env('BILLING_TEAM_BRL_PRICE', 99),
            'interval' => 'month',
            'features' => [
                'Tudo do Pro',
                'Membros ilimitados no time',
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
