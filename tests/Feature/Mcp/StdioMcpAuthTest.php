<?php

use App\Mcp\StdioMcpAuth;
use Laravel\Mcp\Server\Contracts\Transport;

class FakeNonStdioTransport implements Transport
{
    public function onReceive(Closure $handler): void {}

    public function run(): void {}

    public function send(string $message, ?string $sessionId = null): void {}

    public function sessionId(): ?string
    {
        return null;
    }

    public function stream(Closure $stream): void {}
}

test('stdio mcp auth skips when transport is not stdio', function (): void {
    config(['craftdb_mcp.stdio_requires_bearer' => true]);

    StdioMcpAuth::bootstrapIfStdio(new FakeNonStdioTransport);

    expect(true)->toBeTrue();
});
