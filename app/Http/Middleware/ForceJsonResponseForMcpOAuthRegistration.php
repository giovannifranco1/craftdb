<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MCP clients (e.g. Cursor) POST to /oauth/register with a wildcard Accept header or without JSON.
 * Laravel then does not treat the request as expecting JSON, so validation failures become redirects/HTML
 * — the client tries JSON.parse and throws "Unexpected token '<'".
 */
class ForceJsonResponseForMcpOAuthRegistration
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('oauth/register') && $request->isMethod('POST')) {
            $accept = (string) $request->header('Accept', '');

            if ($accept === '' || str_contains($accept, '*/*') || ! str_contains(strtolower($accept), 'json')) {
                $request->headers->set('Accept', 'application/json');
            }
        }

        return $next($request);
    }
}
