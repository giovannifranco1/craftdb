<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Same as Laravel MCP's AddWwwAuthenticateHeader, but never lets URL generation throw.
 * A failing {@see route()} (misconfigured APP_URL, stale route cache, etc.) would turn a 401
 * into a 500 — breaking Cursor's OAuth discovery after auth:api rejects the request.
 */
class McpAddWwwAuthenticateHeader
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($response->getStatusCode() !== 401) {
            return $response;
        }

        $isOauth = app('router')->has('mcp.oauth.protected-resource');
        if ($isOauth) {
            try {
                $metadata = route('mcp.oauth.protected-resource', ['path' => $request->path()]);
            } catch (\Throwable) {
                $path = ltrim($request->path(), '/');
                $metadata = url('/.well-known/oauth-protected-resource'.($path !== '' ? '/'.$path : ''));
            }

            $response->headers->set(
                'WWW-Authenticate',
                'Bearer realm="mcp", resource_metadata="'.$metadata.'"'
            );

            return $response;
        }

        $response->headers->set(
            'WWW-Authenticate',
            'Bearer realm="mcp", error="invalid_token"'
        );

        return $response;
    }
}
