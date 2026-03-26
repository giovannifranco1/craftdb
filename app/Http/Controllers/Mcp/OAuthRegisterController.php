<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mcp;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Url;

/**
 * Dynamic OAuth client registration for MCP (replaces Laravel MCP default).
 *
 * - Allows native-app redirect URIs (e.g. cursor://, vscode://) rejected by Laravel's {@see Url}.
 * - Returns RFC 6749 / RFC 7591 style JSON errors ({ error, error_description }) so Cursor can parse failures.
 */
class OAuthRegisterController
{
    /**
     * @throws BindingResolutionException
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_name' => ['nullable', 'string', 'min:1', 'max:255', 'required_without:name'],
            'name' => ['nullable', 'string', 'min:1', 'max:255', 'required_without:client_name'],
            'redirect_uris' => ['required', 'array', 'min:1'],
            'redirect_uris.*' => array_merge(
                ['required', 'string', 'max:2048'],
                [$this->redirectUriValidator()],
            ),
        ]);

        if ($validator->fails()) {
            return $this->oauthErrorResponse(
                'invalid_client_metadata',
                $validator->errors()->first() ?? 'Invalid client registration request.',
            );
        }

        /** @var array{client_name?: string, name?: string, redirect_uris: array<int, string>} $validated */
        $validated = $validator->validated();

        $clients = Container::getInstance()->make(
            'Laravel\Passport\ClientRepository'
        );

        $client = $clients->createAuthorizationCodeGrantClient(
            name: $validated['client_name'] ?? $validated['name'],
            redirectUris: $validated['redirect_uris'],
            confidential: false,
            user: null,
            enableDeviceFlow: false,
        );

        return response()->json([
            'client_id' => (string) $client->id,
            'grant_types' => $client->grant_types,
            'response_types' => ['code'],
            'redirect_uris' => $client->redirect_uris,
            'scope' => 'mcp:use',
            'token_endpoint_auth_method' => 'none',
        ]);
    }

    /**
     * @return \Closure(string, mixed, \Closure): void
     */
    private function redirectUriValidator(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_string($value) || $value === '') {
                $fail(__('The redirect URI must be a non-empty string.'));

                return;
            }

            if (! $this->isValidOAuthRedirectUri($value)) {
                $fail(__('The redirect URI format is invalid.'));

                return;
            }

            if (in_array('*', config('mcp.redirect_domains', []), true)) {
                return;
            }

            if ($this->hasLocalhostDomain() && $this->isLocalhostUrl($value)) {
                return;
            }

            if (! Str::startsWith($value, $this->allowedDomains())) {
                $fail($attribute.' '.__('is not a permitted redirect domain.'));
            }
        };
    }

    private function oauthErrorResponse(string $error, string $description, int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => $error,
            'error_description' => $description,
        ], $status);
    }

    /**
     * Accepts https/http loopback URLs and custom schemes used by desktop OAuth (cursor://, com.example.app:/path).
     */
    private function isValidOAuthRedirectUri(string $uri): bool
    {
        if (preg_match('#^javascript:#i', $uri) === 1 || preg_match('#^data:#i', $uri) === 1) {
            return false;
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        return (bool) preg_match('#^[a-z][a-z0-9+.-]*:.+#i', $uri);
    }

    private function isLocalhostUrl(string $url): bool
    {
        return Str::startsWith($url, [
            'http://localhost:',
            'http://localhost/',
            'http://127.0.0.1:',
            'http://127.0.0.1/',
            'http://[::1]:',
            'http://[::1]/',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function allowedDomains(): array
    {
        /** @var array<int, string> */
        $allowedDomains = config('mcp.redirect_domains', []);

        return collect($allowedDomains)
            ->map(fn (string $domain): string => Str::endsWith($domain, '/')
                ? $domain
                : "{$domain}/"
            )
            ->all();
    }

    private function hasLocalhostDomain(): bool
    {
        /** @var array<int, string> */
        $domains = config('mcp.redirect_domains', []);

        return collect($domains)->contains(fn (string $domain): bool => in_array(
            rtrim(Str::after($domain, '://'), '/'),
            ['localhost', '127.0.0.1', '[::1]'],
            true,
        ));
    }
}
