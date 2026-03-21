<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Type to Laravel Blueprint Method Mapping
    |--------------------------------------------------------------------------
    |
    | Maps database column types to their corresponding Laravel Blueprint methods.
    | This is the inverse of the migration-parser type_mapping.
    |
    */
    'type_mapping' => [
        'bigint'     => 'bigInteger',
        'int'        => 'integer',
        'mediumint'  => 'mediumInteger',
        'smallint'   => 'smallInteger',
        'tinyint'    => 'tinyInteger',
        'varchar'    => 'string',
        'char'       => 'char',
        'text'       => 'text',
        'mediumtext' => 'mediumText',
        'longtext'   => 'longText',
        'date'       => 'date',
        'datetime'   => 'dateTime',
        'timestamp'  => 'timestamp',
        'time'       => 'time',
        'year'       => 'year',
        'decimal'    => 'decimal',
        'float'      => 'float',
        'double'     => 'double',
        'boolean'    => 'boolean',
        'json'       => 'json',
        'blob'       => 'binary',
        'enum'       => 'enum',
        'set'        => 'set',
        'geometry'   => 'geometry',
        'point'      => 'point',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Lengths (omitted when generating)
    |--------------------------------------------------------------------------
    |
    | When a column has one of these default lengths, it won't be emitted
    | in the generated migration since Laravel uses these defaults.
    |
    */
    'default_lengths' => [
        'varchar' => 255,
        'char'    => 36,
    ],

    /*
    |--------------------------------------------------------------------------
    | Special Method Detection
    |--------------------------------------------------------------------------
    |
    | Patterns for detecting columns that map to Laravel shorthand methods.
    |
    */
    'special_methods' => [
        'id' => [
            'columns' => ['id'],
            'criteria' => ['type' => 'bigint', 'is_primary' => true],
            'method'   => '$table->id();',
        ],
        'timestamps' => [
            'columns'  => ['created_at', 'updated_at'],
            'criteria' => ['type' => 'timestamp'],
            'method'   => '$table->timestamps();',
        ],
        'softDeletes' => [
            'columns'  => ['deleted_at'],
            'criteria' => ['type' => 'timestamp'],
            'method'   => '$table->softDeletes();',
        ],
        'rememberToken' => [
            'columns'  => ['remember_token'],
            'criteria' => ['type' => 'varchar'],
            'method'   => '$table->rememberToken();',
        ],
    ],
];
