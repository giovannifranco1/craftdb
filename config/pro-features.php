<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pro Feature Definitions
    |--------------------------------------------------------------------------
    |
    | Metadata for features that require a Pro plan. Used by the pro-feature
    | modal to display contextual upgrade messages.
    |
    */

    'features' => [
        'import' => [
            'name' => 'Import',
            'description' => 'Bring in schemas from SQL dumps or Laravel migration files and turn them into tables on your canvas.',
            'icon' => 'arrow-up-tray',
        ],

        'export' => [
            'name' => 'JSON export',
            'description' => 'Download your diagram structure as a portable JSON file for backups, versioning, or integration with other tools.',
            'icon' => 'arrow-down-tray',
        ],

        'export-migration' => [
            'name' => 'Laravel migrations',
            'description' => 'Generate Laravel migration classes from your diagram so schema changes live in code and ship with your application.',
            'icon' => 'code-bracket',
        ],

        'graph' => [
            'name' => 'Graph view',
            'description' => 'See your tables as an interactive graph: foreign keys show how everything connects. Pan, zoom, and drag nodes to understand the structure at a glance.',
            'icon' => 'graph',
        ],

        'areas' => [
            'name' => 'Areas',
            'description' => 'Wrap related tables in colored regions to keep large diagrams organized and easier for collaborators to scan.',
            'icon' => 'add-area',
        ],

        'notes' => [
            'name' => 'Sticky notes',
            'description' => 'Place notes anywhere on the canvas to capture decisions, reminders, and context beyond what columns and tables express.',
            'icon' => 'add-note',
        ],
    ],

];
