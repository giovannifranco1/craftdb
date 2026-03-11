<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperRoot
{
    /**
     * Handle an incoming request. Only user with id 1 (super root) can proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->id !== 1) {
            abort(403, __('Unauthorized. Only super root can access this page.'));
        }

        return $next($request);
    }
}
