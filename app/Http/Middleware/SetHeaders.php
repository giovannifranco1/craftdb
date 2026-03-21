<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * SECURITY: This middleware is the trust boundary for the CF-IPCountry header.
     *
     * BillingController reads CF-IPCountry to determine the user's country for pricing.
     * Without this middleware, any client could send a forged CF-IPCountry header and
     * receive pricing intended for a different country.
     *
     * Trust model:
     * - CLOUDFLARE_PROXY=false (default): header is stripped on every request, so
     *   BillingController can never see a client-supplied value.
     * - CLOUDFLARE_PROXY=true: header is passed through as-is. This setting MUST only
     *   be enabled in environments where ALL inbound traffic is routed through Cloudflare,
     *   so the header is guaranteed to be set by Cloudflare and not by the client.
     *
     * Do NOT set CLOUDFLARE_PROXY=true when running behind a load balancer or reverse
     * proxy that is not Cloudflare, or when direct access to the origin is possible.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local')) {
            $request->headers->set('CF-IPCountry', 'BR');
        } elseif (! config('app.cloudflare_proxy', false)) {
            /**
             * Strip CF-IPCountry when not behind Cloudflare to prevent header spoofing.
             * Set CLOUDFLARE_PROXY=true in production when traffic flows through Cloudflare.
            */
            $request->headers->remove('CF-IPCountry');
        }

        return $next($request);
    }
}
