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
            'description' => 'Free plan for personal projects and learning.',
            'price_id' => null,
            'price' => 0,
            'interval' => null,
            'features' => [
                'Up to 3 diagrams',
                'Single-user workspace',
                'Public diagram links',
                'Email support',
            ],
        ],

        'pro_usd' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'For developers and freelancers who need more power.',
            'price_id' => env('STRIPE_PRICE_PRO_USD', 'price_pro_usd'),
            'price' => 9.99,
            'interval' => 'month',
            'features' => [
                'Unlimited diagrams',
                'Unlimited tables per diagram',
                'SQL and Laravel migration import',
                'Public diagram links',
                'Areas, sticky notes, and graph view',
                'Solo use (no teams)',
                'Priority support',
            ],
        ],

        'pro_brl' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'For developers and freelancers who need more power.',
            'price_id' => env('STRIPE_PRICE_PRO_BRL', 'price_pro_brl'),
            'price' => 29.99,
            'interval' => 'month',
            'features' => [
                'Unlimited diagrams',
                'Unlimited tables per diagram',
                'SQL and Laravel migration import',
                'Public diagram links',
                'Areas, sticky notes, and graph view',
                'Solo use (no teams)',
                'Priority support',
            ],
        ],

        'pro_usd_annual' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'For developers and freelancers who need more power.',
            'price_id' => env('STRIPE_PRICE_PRO_USD_ANNUAL', 'price_pro_usd_annual'),
            'price' => 99.99,
            'interval' => 'year',
            'features' => [
                'Unlimited diagrams',
                'Unlimited tables per diagram',
                'SQL and Laravel migration import',
                'Public diagram links',
                'Areas, sticky notes, and graph view',
                'Solo use (no teams)',
                'Priority support',
            ],
        ],

        'pro_brl_annual' => [
            'name' => 'Pro',
            'emoji' => '⚡',
            'description' => 'For developers and freelancers who need more power.',
            'price_id' => env('STRIPE_PRICE_PRO_BRL_ANNUAL', 'price_pro_brl_annual'),
            'price' => 299.99,
            'interval' => 'year',
            'features' => [
                'Unlimited diagrams',
                'Unlimited tables per diagram',
                'SQL and Laravel migration import',
                'Public diagram links',
                'Areas, sticky notes, and graph view',
                'Solo use (no teams)',
                'Priority support',
            ],
        ],

        'team_usd' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'For teams collaborating on database schemas together.',
            'price_id' => env('STRIPE_PRICE_TEAM_USD', 'price_team_usd'),
            'price' => 24.99,
            'interval' => 'month',
            'features' => [
                'Everything in Pro',
                'Up to 25 members per team',
                'Multiple teams',
                'Per-member roles (reader, editor, owner)',
                'View history',
                'Dedicated support',
            ],
        ],

        'team_brl' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'For teams collaborating on database schemas together.',
            'price_id' => env('STRIPE_PRICE_TEAM_BRL', 'price_team_brl'),
            'price' => 59.99,
            'interval' => 'month',
            'features' => [
                'Everything in Pro',
                'Up to 25 members per team',
                'Multiple teams',
                'Per-member roles (reader, editor, owner)',
                'View history',
                'Dedicated support',
            ],
        ],

        'team_usd_annual' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'For teams collaborating on database schemas together.',
            'price_id' => env('STRIPE_PRICE_TEAM_USD_ANNUAL', 'price_team_usd_annual'),
            'price' => 219.99,
            'interval' => 'year',
            'features' => [
                'Everything in Pro',
                'Up to 25 members per team',
                'Multiple teams',
                'Per-member roles (reader, editor, owner)',
                'View history',
                'Dedicated support',
            ],
        ],

        'team_brl_annual' => [
            'name' => 'Team',
            'emoji' => '🚀',
            'description' => 'For teams collaborating on database schemas together.',
            'price_id' => env('STRIPE_PRICE_TEAM_BRL_ANNUAL', 'price_team_brl_annual'),
            'price' => 599.99,
            'interval' => 'year',
            'features' => [
                'Everything in Pro',
                'Up to 25 members per team',
                'Multiple teams',
                'Per-member roles (reader, editor, owner)',
                'View history',
                'Dedicated support',
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
