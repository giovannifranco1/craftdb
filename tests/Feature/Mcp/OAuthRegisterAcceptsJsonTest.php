<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

beforeEach(function (): void {
    Artisan::call('passport:client', [
        '--personal' => true,
        '--name' => 'Test MCP OAuth Register',
        '--provider' => 'users',
    ]);
});

test('oauth register returns oauth-shaped json error when accept is wildcard', function (): void {
    $response = $this->call('POST', '/oauth/register', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => '*/*',
    ], '{}');

    $response->assertStatus(400)
        ->assertHeader('content-type', 'application/json')
        ->assertJsonStructure(['error', 'error_description']);
});

test('oauth register returns json client payload on success', function (): void {
    $response = $this->call('POST', '/oauth/register', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => '*/*',
    ], json_encode([
        'client_name' => 'Cursor MCP',
        'redirect_uris' => ['http://127.0.0.1/callback'],
    ]));

    $response->assertOk()
        ->assertHeader('content-type', 'application/json')
        ->assertJsonStructure(['client_id', 'redirect_uris', 'scope']);
});

test('oauth register accepts custom-scheme redirect URIs for desktop OAuth', function (): void {
    $response = $this->call('POST', '/oauth/register', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => '*/*',
    ], json_encode([
        'client_name' => 'Cursor',
        'redirect_uris' => ['cursor://anysphere.cursor-deeplink/mcp/oauth/callback'],
    ]));

    $response->assertOk()
        ->assertJsonPath('redirect_uris.0', 'cursor://anysphere.cursor-deeplink/mcp/oauth/callback');
});

test('oauth register returns oauth error for javascript redirect URI', function (): void {
    $response = $this->call('POST', '/oauth/register', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => '*/*',
    ], json_encode([
        'client_name' => 'Bad',
        'redirect_uris' => ['javascript:alert(1)'],
    ]));

    $response->assertStatus(400)
        ->assertJsonPath('error', 'invalid_client_metadata')
        ->assertJsonStructure(['error', 'error_description']);
});
