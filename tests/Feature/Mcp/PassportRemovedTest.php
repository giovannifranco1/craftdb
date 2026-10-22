<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

test('MCP06 laravel passport is not installed', function (): void {
    expect(class_exists('Laravel\\Passport\\Passport'))->toBeFalse();
});

test('MCP07 no oauth or passport routes are registered', function (): void {
    foreach (['passport.authorizations.authorize', 'passport.token', 'mcp.authorize.approve', 'mcp.oauth.protected-resource', 'mcp.oauth.authorization-server'] as $name) {
        expect(Route::has($name))->toBeFalse();
    }

    $uris = collect(Route::getRoutes())->map(fn ($r) => $r->uri())
        ->filter(fn ($u) => str_starts_with($u, 'oauth/') || str_starts_with($u, '.well-known/oauth') || str_starts_with($u, 'api/'));

    expect($uris->all())->toBe([]);
});

test('MCP08 user model and app have no passport leftovers', function (): void {
    expect(config('auth.guards.api'))->toBeNull()
        ->and(method_exists(User::class, 'createToken'))->toBeFalse()
        ->and(collect(class_uses_recursive(User::class))->filter(fn ($t) => str_starts_with($t, 'Laravel\\Passport')))->toBeEmpty()
        ->and(collect(class_implements(User::class))->filter(fn ($i) => str_starts_with($i, 'Laravel\\Passport')))->toBeEmpty()
        ->and(class_exists('App\\Mcp\\StdioMcpAuth'))->toBeFalse()
        ->and(class_exists('App\\Models\\McpTokenTeam'))->toBeFalse()
        ->and(class_exists('App\\Mcp\\Registrar'))->toBeFalse();
});

test('MCP09 passport and mcp token tables do not exist', function (string $table): void {
    expect(Schema::hasTable($table))->toBeFalse();
})->with(['oauth_auth_codes', 'oauth_access_tokens', 'oauth_refresh_tokens', 'oauth_clients', 'oauth_device_codes', 'mcp_token_teams']);

test('MCP10 env example, compose and cursor config carry no token setup', function (): void {
    expect(file_get_contents(base_path('.env.example')))->not->toMatch('/PASSPORT_|MCP_BEARER|MCP_STDIO/')
        ->and(file_get_contents(base_path('docker-compose.yml')))->not->toMatch('/PASSPORT_|MCP_BEARER|MCP_STDIO/');

    $raw = file_get_contents(base_path('.cursor/mcp.json'));
    $cursor = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

    expect($cursor['mcpServers']['craftdb']['url'])->toEndWith('/mcp/craftdb')
        ->and($cursor['mcpServers']['craftdb'])->not->toHaveKey('env')
        ->and($raw)->not->toMatch('/env|token|bearer/i');
});
