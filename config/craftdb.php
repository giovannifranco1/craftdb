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
    | Repositório e Buy Me a Coffee são os links oficiais do projeto (fixos);
    | os demais são opcionais e vêm do .env.
    |
    */

    'support' => [
        'repo_url' => 'https://github.com/giovannifranco1/craftdb',
        'github_sponsors_url' => env('CRAFTDB_SUPPORT_GITHUB_SPONSORS_URL'),
        'kofi_url' => env('CRAFTDB_SUPPORT_KOFI_URL'),
        'buymeacoffee_url' => 'https://buymeacoffee.com/craftdb',
        'pix_key' => env('CRAFTDB_SUPPORT_PIX_KEY'),
    ],

];
