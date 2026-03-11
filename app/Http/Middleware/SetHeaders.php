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
