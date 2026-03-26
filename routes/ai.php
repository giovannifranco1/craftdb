<?php

use App\Http\Controllers\Mcp\AuthorizeController;
use App\Mcp\Servers\CraftdbServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

// Intercepts the OAuth approve POST to capture team selection before Passport processes it.
Route::post('/oauth/authorize/teams', [AuthorizeController::class, 'approve'])
    ->middleware(['web', 'auth'])
    ->name('mcp.authorize.approve');

Mcp::local('craftdb', CraftdbServer::class);

Mcp::web('/mcp/craftdb', CraftdbServer::class)
    ->middleware(['auth:api', 'throttle:mcp']);
