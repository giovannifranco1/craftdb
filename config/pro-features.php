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
            'description' => 'Import SQL and Laravel migrations into your diagram.',
            'icon' => 'arrow-up-tray',
        ],

        'graph' => [
            'name' => 'Graph View',
            'description' => 'Visualize your database schema as an interactive graph with relationships.',
            'icon' => 'graph',
        ],

        'areas' => [
            'name' => 'Areas',
            'description' => 'Organize your diagram with colored areas to group related tables.',
            'icon' => 'add-area',
        ],

        'notes' => [
            'name' => 'Sticky Notes',
            'description' => 'Add sticky notes to document and annotate your diagram.',
            'icon' => 'add-note',
        ],
    ],

];
