<?php

return [
    'free' => [
        'diagram_limit' => 3,
        'table_limit' => 30,
        'table_columns_limit' => 50,
    ],

    'pro' => [
        'diagram_limit' => 'unlimited',
        'table_limit' => 100,
        'table_columns_limit' => 100,
        // no member_limit — Pro is individual, no team features
    ],

    'team' => [
        'diagram_limit' => 'unlimited',
        'table_limit' => 100,
        'table_columns_limit' => 100,
        'member_limit' => 24, // owner + 24 members = 25 total
    ],
];
