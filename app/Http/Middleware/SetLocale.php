<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            $locale = Session::get('locale');

            // Validate locale is supported
            if (in_array($locale, ['en', 'pt_BR'])) {
                App::setLocale($locale);
            }

            return $next($request);
        }

        if ($this->shouldUsePortugueseBrazilFromCloudflare($request)) {
            App::setLocale('pt_BR');
        }

        return $next($request);
    }

    /**
     * Guests from Brazil (Cloudflare CF-IPCountry) see auth and landing in pt_BR when no session locale.
     * Requires SetHeaders to run first so the header is trusted or stripped.
     */
    private function shouldUsePortugueseBrazilFromCloudflare(Request $request): bool
    {
        if (strtoupper((string) $request->header('CF-IPCountry')) !== 'BR') {
            return false;
        }

        return $request->routeIs(
            'login',
            'login.store',
            'register',
            'register.store',
            'landing.*',
        );
    }
}
