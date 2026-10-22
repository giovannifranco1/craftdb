<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks browser-originated requests from foreign sites (DNS rebinding / CSRF-like
 * attacks against the token-less local MCP endpoint). Non-browser clients send no Origin.
 */
class EnsureLocalMcpOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');

        if ($origin === null) {
            return $next($request);
        }

        if (! $this->isAllowedOrigin($origin)) {
            abort(403, 'Origin not allowed.');
        }

        return $next($request);
    }

    private function isAllowedOrigin(string $origin): bool
    {
        // parse_url and browsers disagree on how to treat a backslash (e.g. "http://evil.com\\@localhost"),
        // which would let a foreign origin pass the host check. Reject outright.
        if (str_contains($origin, '\\')) {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        return is_string($host) && $host !== '' && in_array(strtolower($host), $this->allowedHosts(), true);
    }

    /**
     * @return list<string>
     */
    private function allowedHosts(): array
    {
        $hosts = ['localhost', '127.0.0.1', '[::1]', '::1'];

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if (is_string($appHost) && $appHost !== '') {
            $hosts[] = strtolower($appHost);
        }

        return $hosts;
    }
}
