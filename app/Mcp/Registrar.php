<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Http\Controllers\Mcp\OAuthRegisterController;
use App\Http\Middleware\McpAddWwwAuthenticateHeader;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Middleware\ReorderJsonAccept;
use Laravel\Mcp\Server\Registrar as BaseRegistrar;
use Laravel\Mcp\Server\Transport\HttpTransport;

/**
 * Uses {@see McpAddWwwAuthenticateHeader} so OAuth metadata URLs cannot trigger a 500 on 401 responses.
 *
 * @see BaseRegistrar::web()
 */
class Registrar extends BaseRegistrar
{
    /**
     * @param  class-string<Server>  $serverClass
     */
    public function web(string $route, string $serverClass): Route
    {
        Router::get($route, fn (): Response => response('', 405)->header('Allow', 'POST'));

        Router::delete($route, fn (): Response => response('', 405)->header('Allow', 'POST'));

        $route = Router::post($route, fn (): mixed => static::startServer(
            $serverClass,
            fn (): HttpTransport => new HttpTransport(
                $request = request(),
                (string) $request->header('MCP-Session-Id')
            ),
        ))->middleware([
            ReorderJsonAccept::class,
            McpAddWwwAuthenticateHeader::class,
        ]);

        assert($route instanceof Route);

        $this->httpServers[$route->uri()] = $route;

        return $route;
    }

    /**
     * Use CraftDB OAuth registration so redirect URIs like cursor:// are accepted and errors match OAuth JSON shape.
     *
     * @see BaseRegistrar::oauthRoutes()
     */
    public function oauthRoutes(string $oauthPrefix = 'oauth'): void
    {
        static::ensureMcpScope();
        Router::get('/.well-known/oauth-protected-resource/{path?}', fn (?string $path = '') => response()->json([
            'resource' => url('/'.$path),
            'authorization_servers' => [url('/'.$path)],
            'scopes_supported' => ['mcp:use'],
        ]))->where('path', '.*')->name('mcp.oauth.protected-resource');

        Router::get('/.well-known/oauth-authorization-server/{path?}', fn (?string $path = '') => response()->json([
            'issuer' => url('/'.$path),
            'authorization_endpoint' => route('passport.authorizations.authorize'),
            'token_endpoint' => route('passport.token'),
            'registration_endpoint' => url($oauthPrefix.'/register'),
            'response_types_supported' => ['code'],
            'code_challenge_methods_supported' => ['S256'],
            'scopes_supported' => ['mcp:use'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
        ]))->where('path', '.*')->name('mcp.oauth.authorization-server');

        Router::post($oauthPrefix.'/register', OAuthRegisterController::class);
    }
}
