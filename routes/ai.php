<?php

use App\Http\Middleware\EnsureLocalMcpOrigin;
use App\Mcp\Servers\CraftdbServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('craftdb', CraftdbServer::class);

Mcp::web('/mcp/craftdb', CraftdbServer::class)
    ->middleware([EnsureLocalMcpOrigin::class, 'throttle:mcp']);
