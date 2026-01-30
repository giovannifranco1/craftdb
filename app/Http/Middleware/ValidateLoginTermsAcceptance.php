<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Symfony\Component\HttpFoundation\Response;

class ValidateLoginTermsAcceptance
{
    /**
     * Handle an incoming request.
     * Validates that the user accepted terms and privacy policy on login form.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') || ! $request->routeIs('login.store')) {
            return $next($request);
        }

        $validator = ValidatorFacade::make($request->all(), [
            'accept_terms' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => __('auth.accept_terms_required'),
            'accept_terms.accepted' => __('auth.accept_terms_accepted'),
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('login')
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        return $next($request);
    }
}
