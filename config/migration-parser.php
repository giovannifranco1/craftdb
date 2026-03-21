<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Migration Parser Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all the patterns and mappings used
    | to parse Laravel migration files into database diagrams.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Laravel Type to Database Type Mapping
    |--------------------------------------------------------------------------
    |
    | Maps Laravel column types to their corresponding database types.
    |
    */
    'type_mapping' => [
        // Integer types
        'id' => 'bigint',
        'bigInteger' => 'bigint',
        'integer' => 'int',
        'mediumInteger' => 'mediumint',
        'smallInteger' => 'smallint',
        'tinyInteger' => 'tinyint',
        'unsignedBigInteger' => 'bigint',
        'unsignedInteger' => 'int',
        'unsignedMediumInteger' => 'mediumint',
        'unsignedSmallInteger' => 'smallint',
        'unsignedTinyInteger' => 'tinyint',

        // Foreign key types
        'foreignId' => 'bigint',
        'foreignIdFor' => 'bigint',
        'foreignUuid' => 'char',
        'foreignUlid' => 'char',

        // String types
        'string' => 'varchar',
        'char' => 'char',
        'text' => 'text',
        'mediumText' => 'mediumtext',
        'longText' => 'longtext',

        // Date and time types
        'date' => 'date',
        'dateTime' => 'datetime',
        'dateTimeTz' => 'datetime',
        'time' => 'time',
        'timeTz' => 'time',
        'timestamp' => 'timestamp',
        'timestampTz' => 'timestamp',
        'timestamps' => 'timestamp',
        'year' => 'year',

        // Decimal types
        'decimal' => 'decimal',
        'double' => 'double',
        'float' => 'float',

        // Boolean
        'boolean' => 'tinyint',

        // Binary types
        'binary' => 'blob',

        // JSON
        'json' => 'json',
        'jsonb' => 'json',

        // UUID
        'uuid' => 'char',
        'ulid' => 'char',

        // Enum
        'enum' => 'enum',
        'set' => 'set',

        // Geometry types
        'geometry' => 'geometry',
        'point' => 'point',
        'lineString' => 'linestring',
        'polygon' => 'polygon',
        'multiPoint' => 'multipoint',
        'multiLineString' => 'multilinestring',
        'multiPolygon' => 'multipolygon',
        'geometryCollection' => 'geometrycollection',
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Key Column Types
    |--------------------------------------------------------------------------
    |
    | Column types that should be marked as foreign keys.
    |
    */
    'foreign_key_types' => [
        'foreignId',
        'foreignIdFor',
        'foreignUuid',
        'foreignUlid',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Column Lengths
    |--------------------------------------------------------------------------
    |
    | Default lengths for specific column types when not explicitly defined.
    |
    */
    'default_lengths' => [
        'varchar' => 255,
        'char' => 36, // UUID length
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Key Detection Patterns
    |--------------------------------------------------------------------------
    |
    | Regex patterns to detect different foreign key syntaxes in Laravel migrations.
    | Each pattern has a name, regex, and extraction logic defined in the parser.
    |
    */
    'foreign_key_patterns' => [
        // Pattern 1: Old-style explicit foreign key
        // Example: $table->foreign('user_id')->references('id')->on('users')
        'explicit' => [
            'regex' => '/\$\w+\s*->\s*foreign\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\).*?->\s*references\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\).*?->\s*on\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i',
            'description' => 'Explicit foreign key with foreign(), references(), and on() methods',
            'groups' => [
                1 => 'column_name',
                2 => 'referenced_column',
                3 => 'referenced_table',
            ],
        ],

        // Pattern 2: foreignId with explicit table name
        // Example: $table->foreignId('user_id')->constrained('users')
        // Example: $table->foreignId('diagram_id')->constrained('diagram', indexName: 'diagram_id_foreign')
        // Example: $table->foreignId('from_table_id')->index('...')->constrained('table', indexName: '...')
        'foreign_id_explicit' => [
            'regex' => '/\$\w+\s*->\s*foreignId\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\).*?->\s*constrained\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*[^)]+)?\s*\)/i',
            'description' => 'ForeignId with explicit table name in constrained() (with optional parameters like indexName and intermediate method calls)',
            'groups' => [
                1 => 'column_name',
                2 => 'referenced_table',
            ],
            'referenced_column' => 'id', // Default
        ],

        // Pattern 3: foreignId with inferred table name
        // Example: $table->foreignId('user_id')->constrained()
        'foreign_id_inferred' => [
            'regex' => '/\$\w+\s*->\s*foreignId\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\).*?->\s*constrained\s*\(\s*\)/i',
            'description' => 'ForeignId with inferred table name from column name',
            'groups' => [
                1 => 'column_name',
            ],
            'infer_table' => true, // Table name should be inferred from column name
            'referenced_column' => 'id',
        ],

        // Pattern 4: foreignIdFor with Model class
        // Example: $table->foreignIdFor(User::class)
        // Example: $table->foreignIdFor(User::class, 'author_id')
        'foreign_id_for' => [
            'regex' => '/\$\w+\s*->\s*foreignIdFor\s*\(\s*([A-Za-z_\\\\]+)::class(?:\s*,\s*[\'"]([^\'"]+)[\'"]\s*)?\)/i',
            'description' => 'ForeignIdFor with Model class reference',
            'groups' => [
                1 => 'model_class',
                2 => 'column_name_optional',
            ],
            'infer_from_model' => true,
            'referenced_column' => 'id',
        ],

        // Pattern 5: foreignUuid or foreignUlid with constrained
        // Example: $table->foreignUuid('user_uuid')->constrained()
        // Example: $table->foreignUlid('user_ulid')->constrained('users')
        'foreign_uuid_ulid' => [
            'regex' => '/\$\w+\s*->\s*(?:foreignUuid|foreignUlid)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\).*?->\s*constrained\s*\(\s*(?:[\'"]([^\'"]+)[\'"]\s*)?\)/i',
            'description' => 'ForeignUuid or ForeignUlid with optional table name',
            'groups' => [
                1 => 'column_name',
                2 => 'referenced_table_optional',
            ],
            'infer_table' => true, // If table not provided, infer from column name
            'referenced_column' => 'id',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Extraction Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns to extract Schema blocks from migration files.
    |
    */
    'schema_patterns' => [
        // Optional return type on closure (e.g. function (Blueprint $table): void {) — Laravel / Pint stubs
        'create_table' => '/Schema::create\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*function\s*\([^)]*\)(?:\s*:\s*\w+)?\s*{(.*?)}\s*\)/s',
        'alter_table' => '/Schema::table\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*function\s*\([^)]*\)(?:\s*:\s*\w+)?\s*{(.*?)}\s*\)/s',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Definition Pattern
    |--------------------------------------------------------------------------
    |
    | Pattern to extract column definitions from blueprint code.
    | Captures: column type, column name, and optional parameters (length or array)
    |
    */
    'column_pattern' => '/\$\w+->(\w+)\s*\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(.+?))?\s*\)/i',

    /*
    |--------------------------------------------------------------------------
    | Special Methods
    |--------------------------------------------------------------------------
    |
    | Methods that should be handled specially and not as regular columns.
    |
    */
    'special_methods' => [
        'id' => [
            'columns' => [
                ['name' => 'id', 'type' => 'bigint', 'nullable' => false, 'is_primary' => true],
            ],
        ],
        'timestamps' => [
            'columns' => [
                ['name' => 'created_at', 'type' => 'timestamp', 'nullable' => true],
                ['name' => 'updated_at', 'type' => 'timestamp', 'nullable' => true],
            ],
        ],
        'softDeletes' => [
            'columns' => [
                ['name' => 'deleted_at', 'type' => 'timestamp', 'nullable' => true],
            ],
        ],
        'softDeletesTz' => [
            'columns' => [
                ['name' => 'deleted_at', 'type' => 'timestamp', 'nullable' => true],
            ],
        ],
        'rememberToken' => [
            'columns' => [
                ['name' => 'remember_token', 'type' => 'varchar', 'length' => 100, 'nullable' => true],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Skip Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns to identify lines that should be skipped during column parsing.
    |
    */
    'skip_patterns' => [
        '->foreign(',
        '->index(',
        '->primary(',
        // '->unique(',
        '->dropForeign(',
        '->dropIndex(',
        '->dropPrimary(',
        '->dropUnique(',
        '->dropColumn(',
        '->renameColumn(',
        '->dropTimestamps(',
        '->dropSoftDeletes(',
    ],

    /*
    |--------------------------------------------------------------------------
    | Drop Column Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns to detect column drops in Schema::table() blocks.
    |
    */
    'drop_column_patterns' => [
        // Single column: $table->dropColumn('column_name')
        'single' => '/\$\w+\s*->\s*dropColumn\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i',
        // Multiple columns: $table->dropColumn(['col1', 'col2'])
        'multiple' => '/\$\w+\s*->\s*dropColumn\s*\(\s*\[(.*?)\]\s*\)/is',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modify Column Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns to detect column modifications in Schema::table() blocks.
    |
    */
    'modify_column_pattern' => '/->change\s*\(\s*\)/i',
];
