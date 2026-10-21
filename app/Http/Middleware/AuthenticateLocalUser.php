<?php

namespace App\Http\Middleware;

use App\Actions\Local\EnsureLocalUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CraftDB Local has a single implicit user: log it in once per session.
 */
class AuthenticateLocalUser
{
    public function __construct(private EnsureLocalUser $ensureLocalUser) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            Auth::login($this->ensureLocalUser->exec((string) config('craftdb.local_user.email'))['user']);
        }

        return $next($request);
    }
}
