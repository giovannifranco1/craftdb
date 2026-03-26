<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Require Passport bearer for stdio (mcp:start)
    |--------------------------------------------------------------------------
    |
    | When true, `php artisan mcp:start` validates MCP_BEARER_TOKEN (or the env
    | below) as a Passport access token with the mcp:use scope before accepting
    | JSON-RPC. The token is bound to the app request so tools resolve the user.
    |
    | Set to false only for rare local workflows (not recommended).
    |
    */

    'stdio_requires_bearer' => env('MCP_STDIO_REQUIRES_BEARER', true),

    /*
    |--------------------------------------------------------------------------
    | Name of the environment variable holding the access token (stdio)
    |--------------------------------------------------------------------------
    |
    | Override with CRAFTDB_MCP_STDIO_ENV_NAME if you need a different name.
    |
    */

    'stdio_bearer_env' => env('CRAFTDB_MCP_STDIO_ENV_NAME', 'MCP_BEARER_TOKEN'),

];
