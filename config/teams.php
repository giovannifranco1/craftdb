<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    | Customize middleware behavior and handling of unauthorized requests.
    */
    'middleware' => [

        // Whether to automatically register team middleware in the service provider.
        'register' => true,

        // Response method upon unauthorized access: abort or redirect.
        'handling' => 'abort',

        // Handlers for unauthorized access, aligned with the handling method.
        'handlers' => [
            'abort' => [
                'code' => 403,
                'message' => 'User does not have any of the necessary access rights.',
            ],
            'redirect' => [
                'url' => '/home',
                'message' => [
                    'key' => 'error',
                    'content' => '',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Bindings
    |--------------------------------------------------------------------------
    | Define the models used for team functionalities and role-based access.
    */
    'models' => [
        'user' => App\Models\User::class,
        'team' => App\Models\Team\Team::class,
        'membership' => App\Models\Team\Membership::class,
        'role' => App\Models\Team\Role::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    | Specify table names linked to team-related models.
    */
    'tables' => [
        'teams' => 'team',
        'team_user' => 'team_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Keys
    |--------------------------------------------------------------------------
    | Foreign keys for table relationships in package models.
    */
    'foreign_keys' => [
        'team_id' => 'team_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Invitations
    |--------------------------------------------------------------------------
    | Configures the team invitation feature, allowing users to be invited to join teams.
    */
    'invitations' => [

        'enabled' => true,

        'routes' => [
            'register' => true,
            'url' => '/invitation/{invitation_id}/accept',
            'middleware' => 'web'
        ]
    ],
];
