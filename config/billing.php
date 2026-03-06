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
            'description' => 'Get started with essential features.',
            'price_id' => null,
            'price' => 0,
            'interval' => null,
            'features' => [
                'Up to 3 diagrams',
                'Basic collaboration',
                'Community support',
            ],
        ],
        'pro_monthly' => [
            'name' => 'Pro',
            'description' => 'Unlimited diagrams and priority support.',
            'price_id' => env('STRIPE_PRICE_PRO_MONTHLY', 'price_pro_monthly'),
            'price' => (float) env('BILLING_PRO_MONTHLY_PRICE', 9),
            'interval' => 'month',
            'features' => [
                'Unlimited diagrams',
                'Priority support',
                'Export & advanced sharing',
                'Team features',
            ],
        ],
        'pro_yearly' => [
            'name' => 'Pro (Yearly)',
            'description' => 'Best value — save when you pay annually.',
            'price_id' => env('STRIPE_PRICE_PRO_YEARLY', 'price_pro_yearly'),
            'price' => (float) env('BILLING_PRO_YEARLY_PRICE', 90),
            'interval' => 'year',
            'features' => [
                'Everything in Pro',
                '2 months free',
                'Unlimited diagrams',
                'Priority support',
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
