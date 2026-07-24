<?php

return [
    'meta' => [
        'default_title' => 'CraftDB — The most visual way to design your database',
        'default_description' => 'Create intuitive database diagrams. Define tables, relationships, and work in teams. Zero complexity, maximum productivity.',
        'index_title' => 'CraftDB — The most visual way to design your database',
        'features_title' => 'Features - CraftDB',
        'pricing_title' => 'Pricing - CraftDB',
        'about_title' => 'About - CraftDB',
    ],

    'nav' => [
        'features' => 'Features',
        'pricing' => 'Pricing',
        'how_it_works' => 'How it works',
        'faq' => 'FAQ',
        'dashboard' => 'Dashboard',
        'sign_in' => 'Sign in',
    ],

    'brand' => [
        'beta' => 'Beta',
    ],

    'footer' => [
        'features' => 'Features',
        'pricing' => 'Pricing',
        'terms' => 'Terms',
        'privacy' => 'Privacy',
    ],

    'hero' => [
        'headline_prefix' => 'The most visual way to',
        'headline_gradient' => 'design your database',
        'subheadline' => 'Create intuitive database diagrams. Define tables, relationships, and work in teams. Zero complexity, maximum productivity.',
        'cta_dashboard' => 'Go to Dashboard',
        'cta_register' => 'Start for free',
        'cta_secondary' => 'See features',
        'tech_line' => 'MySQL, PostgreSQL, SQLite, and Laravel Migration',
        'macbook_alt' => 'MacBook',
        'macbook_screen_alt' => 'MacBook screen',
    ],

    'logo_strip' => [
        'title_prefix' => 'Support for many',
        'title_gradient' => 'databases',
        'subtitle' => 'Compatible with leading relational databases and technologies',
    ],

    'features' => [
        'title_line1' => 'Visual modeling,',
        'title_gradient' => 'collaboration',
        'title_line2' => 'and productivity',
        'subtitle' => 'Everything you need to design professional databases visually and collaboratively.',
        'items' => [
            [
                'icon' => 'add-table',
                'title' => 'Visual tables',
                'description' => 'Create tables with fields, types, and constraints visually and intuitively on the canvas.',
            ],
            [
                'icon' => 'graph',
                'title' => 'FK relationships',
                'description' => 'Visualize and create foreign keys connecting tables in the diagram.',
            ],
            [
                'icon' => 'add-area',
                'title' => 'Table groups',
                'description' => 'Organize tables into colored, resizable, nameable groups.',
            ],
            [
                'icon' => 'add-note',
                'title' => 'Notes (Sticky Notes)',
                'description' => 'Add notes and documentation directly on your diagram.',
            ],
            [
                'icon' => 'palette',
                'title' => 'Themes and appearance',
                'description' => 'Dark mode, light mode, and diagram styles (colorful or neutral).',
            ],
            [
                'icon' => 'users',
                'title' => 'Teams and members',
                'description' => 'Organize diagrams in teams, invite members, and define roles (editor/viewer).',
            ],
            [
                'icon' => 'code',
                'title' => 'SQL and Laravel import',
                'description' => 'Import schemas from SQL (MySQL) or Laravel Migration files.',
            ],
            [
                'icon' => 'share',
                'title' => 'Public link',
                'description' => 'Share diagrams via public link with no login required to view.',
            ],
            [
                'icon' => 'heart',
                'title' => 'Favorites',
                'description' => 'Star diagrams for quick access from the dashboard.',
            ],
        ],
    ],

    'how_it_works' => [
        'badge' => 'Simple and powerful',
        'title_line1' => 'From idea to diagram',
        'title_line2' => 'in minutes',
        'subtitle' => 'Four simple steps to create, connect, and share your database schemas professionally.',
        'steps' => [
            [
                'icon' => 'users',
                'title' => 'Create your team',
                'description' => 'Set up your team and manage permissions for efficient collaboration.',
                'color' => 'from-blue-500 to-cyan-500',
            ],
            [
                'icon' => 'add-table',
                'title' => 'Build the diagram',
                'description' => 'Model your database visually—tables, fields, and relationships on an intuitive canvas.',
                'color' => 'from-violet-500 to-purple-500',
            ],
            [
                'icon' => 'code',
                'title' => 'Import your schema',
                'description' => 'Bring your existing structure quickly with SQL or Laravel Migration import.',
                'color' => 'from-yellow-500 to-orange-500',
            ],
            [
                'icon' => 'share',
                'title' => 'Share',
                'description' => 'Generate a public link and share the diagram without requiring login.',
                'color' => 'from-emerald-500 to-teal-500',
            ],
        ],
    ],

    'showcase' => [
        'title_line1' => 'See CraftDB',
        'title_gradient' => 'in action',
        'subtitle' => 'From canvas to collaboration: see how it feels to model, import, and share your databases.',
        'items' => [
            [
                'title' => 'Visual modeling on the canvas',
                'description' => 'Drag tables, define fields and types, and connect foreign keys on an infinite canvas. All visual—no hand-written SQL.',
                'bullets' => [
                    'Tables, fields, types, and constraints',
                    'One-click FK relationships',
                    'Colored areas and sticky notes',
                ],
                'image' => 'resources/images/landing-page/showcase-canvas.png',
                'alt' => 'CraftDB canvas with tables and relationships',
            ],
            [
                'title' => 'SQL and Laravel Migration import',
                'description' => 'Already have a schema? Bring it in seconds by importing a SQL file (MySQL) or your Laravel Migrations. CraftDB detects tables, fields, and relationships automatically.',
                'bullets' => [
                    'Import .sql (MySQL)',
                    'Import Laravel Migration files',
                    'Automatic foreign key detection',
                ],
                'image' => 'resources/images/landing-page/showcase-import.png',
                'alt' => 'CraftDB schema import screen',
            ],
            [
                'title' => 'Team collaboration and public link',
                'description' => 'Organize diagrams by team, invite members with editor or viewer roles, and share via public link—no login required to view.',
                'bullets' => [
                    'Teams with roles (editor / viewer)',
                    'Public link with no login required',
                    'Diagrams organized by team',
                ],
                'image' => 'resources/images/landing-page/showcase-collab.png',
                'alt' => 'Sharing and collaboration in CraftDB',
            ],
        ],
    ],

    'pricing' => [
        'title_line1' => 'Simple pricing,',
        'title_gradient' => 'no surprises',
        'subtitle' => 'Start free and scale as you need. No credit card required to get started.',
        'billing_monthly' => 'Monthly',
        'billing_annual' => 'Annual',
        'save_badge' => 'Save ~17%',
        'plans' => [
            'hobby' => [
                'name' => 'Hobby',
                'price' => 'Free',
                'description' => 'For personal projects and learning.',
                'features' => [
                    '3 diagrams',
                    'Individual use',
                    'Public diagram links',
                    'Email support',
                ],
                'cta' => 'Start for free',
            ],
            'pro' => [
                'name' => 'Pro',
                'badge' => 'Most popular',
                'price_monthly' => 'R$29.99',
                'price_annual' => 'R$299.99',
                'period_monthly' => '/month',
                'period_annual' => '/year',
                'annual_equivalent' => '≈ R$25/month',
                'description' => 'For developers and freelancers who need more.',
                'features' => [
                    'Unlimited diagrams',
                    'Unlimited tables per diagram',
                    'SQL / Laravel Migration import',
                    'Public diagram links',
                    'Areas, Sticky Notes, Graph View',
                    'Individual use (no teams)',
                    'Priority support',
                ],
                'cta' => 'Subscribe to Pro',
            ],
            'team' => [
                'name' => 'Team',
                'price_monthly' => 'R$59.99',
                'price_annual' => 'R$599.99',
                'period_monthly' => '/month',
                'period_annual' => '/year',
                'annual_equivalent' => '≈ R$50/month',
                'description' => 'For teams working together on schemas.',
                'features' => [
                    'Everything in Pro',
                    'Up to 25 users per team',
                    'Multiple teams',
                    'Per-member permissions (viewer, editor, owner)',
                    'View history',
                    'Dedicated support',
                ],
                'cta' => 'Subscribe to Team',
            ],
        ],
    ],

    'faq' => [
        'title_line1' => 'Frequently asked',
        'title_gradient' => 'questions',
        'subtitle' => 'Get answers about CraftDB.',
        'items' => [
            [
                'question' => 'Can I export my diagrams to SQL?',
                'answer' => 'SQL export is in development. Today you can import schemas from SQL files (MySQL) and Laravel Migration files.',
            ],
            [
                'question' => 'Which databases and technologies are supported?',
                'answer' => 'For import: SQL (MySQL) and Laravel Migration files. CraftDB supports visual modeling for MySQL, PostgreSQL, and SQLite. More formats are coming.',
            ],
            [
                'question' => 'How does team collaboration work?',
                'answer' => 'You create a team, invite members by email, and assign each person a role (editor or viewer). Diagrams are organized by team, and you can share a public link with people who do not have an account.',
            ],
            [
                'question' => 'Can I import existing schemas?',
                'answer' => 'Yes. Import from SQL (MySQL) files or Laravel Migration (.php) files. CraftDB detects tables, columns, and relationships automatically.',
            ],
            [
                'question' => 'Are diagrams saved in the cloud?',
                'answer' => 'Yes—all diagrams are saved in the cloud. Access them from any device with your account.',
            ],
            [
                'question' => 'Is there a limit on tables per diagram?',
                'answer' => 'Yes. There is currently a limit of 30 tables per diagram to keep canvas performance smooth.',
            ],
            [
                'question' => 'Can I use it offline?',
                'answer' => 'CraftDB works online only for now. Data lives in the cloud and you need internet to access and edit.',
            ],
            [
                'question' => 'Do I need a credit card to start?',
                'answer' => 'No. You can create an account and use the free plan without entering a credit card.',
            ],
        ],
    ],

    'statistics' => [
        'items' => [
            ['icon' => 'users', 'value' => '5,000+', 'label' => 'Active developers'],
            ['icon' => 'code', 'value' => '50,000+', 'label' => 'Diagrams created'],
            ['icon' => 'clock', 'value' => '99.9%', 'label' => 'Uptime'],
            ['icon' => 'star', 'value' => '4.9', 'label' => 'Average rating'],
        ],
    ],

    'testimonials' => [
        'title_line1' => 'Loved by',
        'title_gradient' => 'developers',
        'subtitle' => 'See what tech professionals are saying about CraftDB.',
        'items' => [
            [
                'quote' => 'The visual experience is great. We organize diagrams in teams and the public link makes it easy to share with clients.',
                'author' => 'Carlos Silva',
                'role' => 'Tech Lead @ Fintech Brasil',
                'avatar' => 'CS',
                'rating' => 5,
            ],
            [
                'quote' => 'Sticky notes are perfect for documenting architecture decisions. I can explain the “why” right on the diagram.',
                'author' => 'Marina Costa',
                'role' => 'DBA @ E-commerce Plus',
                'avatar' => 'MC',
                'rating' => 5,
            ],
            [
                'quote' => 'Laravel Migration import saved a ton of time. I already had migrations and the diagram was ready in a few clicks.',
                'author' => 'João Santos',
                'role' => 'Backend Developer @ SaaS Startup',
                'avatar' => 'JS',
                'rating' => 5,
            ],
            [
                'quote' => 'Areas on the canvas help separate domains in the model. Much more readable than a pile of loose tables.',
                'author' => 'Ana Oliveira',
                'role' => 'Software Architect @ Consultancy',
                'avatar' => 'AO',
                'rating' => 5,
            ],
            [
                'quote' => 'The interface is intuitive. Our team started using it and soon everyone was modeling without training.',
                'author' => 'Pedro Lima',
                'role' => 'CTO @ Agency Digital',
                'avatar' => 'PL',
                'rating' => 5,
            ],
            [
                'quote' => 'Dark mode and diagram themes matter when you spend hours modeling. Less eye strain.',
                'author' => 'Fernanda Rocha',
                'role' => 'Full Stack Developer @ Tech Corp',
                'avatar' => 'FR',
                'rating' => 5,
            ],
        ],
    ],
];
