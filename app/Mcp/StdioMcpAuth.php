<?php

namespace App\Mcp;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Server\Contracts\Transport;
use Laravel\Mcp\Server\Transport\StdioTransport;

final class StdioMcpAuth
{
    public static function bootstrapIfStdio(Transport $transport): void
    {
        if (! $transport instanceof StdioTransport) {
            return;
        }

        if (! config('craftdb_mcp.stdio_requires_bearer', true)) {
            return;
        }

        $envName = (string) config('craftdb_mcp.stdio_bearer_env', 'MCP_BEARER_TOKEN');
        $plainToken = env($envName);

        if ($plainToken === null || $plainToken === '') {
            self::failStdio(
                "CraftDB MCP (stdio): missing environment variable [{$envName}].\n".
                "Create a Passport personal access token with scope mcp:use, e.g. in Tinker:\n".
                "  \$user = App\\Models\\User::first();\n".
                "  \$user->createToken('mcp-local', ['mcp:use'])->accessToken\n".
                "Then set {$envName} in your shell or Cursor MCP server `env` block.\n"
            );
        }

        $request = Request::create('/mcp/craftdb', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json, text/event-stream',
            'CONTENT_TYPE' => 'application/json',
        ]);
        $request->headers->set('Authorization', 'Bearer '.$plainToken);

        app()->instance('request', $request);

        Auth::shouldUse('api');

        $guard = Auth::guard('api');
        if (method_exists($guard, 'setRequest')) {
            $guard->setRequest($request);
        }

        $user = $guard->user();

        if (! $user instanceof User) {
            self::failStdio(
                "CraftDB MCP (stdio): invalid or expired bearer token for [{$envName}].\n"
            );
        }

        if ($user->tokenCant('mcp:use')) {
            self::failStdio(
                "CraftDB MCP (stdio): token is missing the [mcp:use] scope.\n"
            );
        }
    }

    private static function failStdio(string $message): never
    {
        fwrite(STDERR, $message);

        exit(1);
    }
}
