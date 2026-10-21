<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Usuário local (CraftDB Local)
    |--------------------------------------------------------------------------
    |
    | Criado no boot do container pelo comando `craftdb:local-user`.
    |
    */

    'local_user' => [
        'email' => env('CRAFTDB_USER_EMAIL', 'local@craftdb.local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Forçar HTTPS
    |--------------------------------------------------------------------------
    |
    | Fora de local/testing as URLs são forçadas para HTTPS. O docker-compose
    | local seta false, já que o app roda em http://localhost.
    |
    */

    'force_https' => (bool) env('APP_FORCE_HTTPS', true),

];
