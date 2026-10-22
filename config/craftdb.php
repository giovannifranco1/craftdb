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

    /*
    |--------------------------------------------------------------------------
    | Apoiar o projeto
    |--------------------------------------------------------------------------
    |
    | Links da página /support. Links nulos não aparecem na página.
    |
    */

    'support' => [
        'repo_url' => env('CRAFTDB_SUPPORT_REPO_URL'),
        'github_sponsors_url' => env('CRAFTDB_SUPPORT_GITHUB_SPONSORS_URL'),
        'kofi_url' => env('CRAFTDB_SUPPORT_KOFI_URL'),
        'buymeacoffee_url' => env('CRAFTDB_SUPPORT_BUYMEACOFFEE_URL'),
        'pix_key' => env('CRAFTDB_SUPPORT_PIX_KEY'),
    ],

];
