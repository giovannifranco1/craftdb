<?php

use App\Actions\Local\EnsureLocalUser;
use App\Models\Diagram;
use App\Models\Team\Team;
use App\Models\User;

function mcpInitialize(array $headers = [])
{
    return test()->withHeaders(array_merge([
        'Accept' => 'application/json, text/event-stream',
    ], $headers))->postJson('/mcp/craftdb', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => [],
            'clientInfo' => ['name' => 'test', 'version' => '1.0'],
        ],
    ]);
}

function mcpCallTool(string $name, array $arguments = [], array $headers = [])
{
    $init = mcpInitialize($headers);
    $sessionId = $init->headers->get('MCP-Session-Id');

    return test()->withHeaders(array_merge([
        'Accept' => 'application/json, text/event-stream',
        'MCP-Session-Id' => $sessionId,
    ], $headers))->postJson('/mcp/craftdb', [
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/call',
        'params' => ['name' => $name, 'arguments' => (object) $arguments],
    ]);
}

function localMcpUser(): User
{
    return app(EnsureLocalUser::class)->exec((string) config('craftdb.local_user.email'))['user'];
}

test('MCP01 initialize works without token and without Origin', function (): void {
    mcpInitialize()
        ->assertOk()
        ->assertJsonPath('jsonrpc', '2.0')
        ->assertJsonPath('id', 1)
        ->assertJsonPath('result.serverInfo.name', 'CraftDB');
});

test('MCP01 initialize works with local Origins', function (string $origin): void {
    mcpInitialize(['Origin' => $origin])
        ->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'CraftDB');
})->with([
    'http://localhost:8080',
    'http://127.0.0.1:8080',
    'http://[::1]:8080',
    'HTTP://LOCALHOST',
]);

test('MCP01 initialize works with the APP_URL host Origin', function (): void {
    config(['app.url' => 'https://craftdb.test']);

    mcpInitialize(['Origin' => 'https://craftdb.test'])->assertOk();
});

test('MCP02 foreign Origin is rejected with 403 and no tool runs', function (string $origin): void {
    $user = localMcpUser();
    $diagram = Diagram::factory()->create(['team_id' => $user->ownedTeams()->first()->id]);

    mcpCallTool('delete_diagram', ['diagram_id' => $diagram->id], ['Origin' => $origin])
        ->assertForbidden();

    expect(Diagram::find($diagram->id))->not->toBeNull();
})->with([
    'http://evil.example',
    'null',
    '',
    'http://localhost.evil.example',
    'http://localhost@evil.com',
    'http://evil.com#@localhost',
    'http://evil.com\\@localhost',
]);

test('MCP02 foreign Origin on initialize is 403', function (): void {
    mcpInitialize(['Origin' => 'http://evil.example'])->assertForbidden();
});

test('MCP03 list_teams and list_diagrams only show the local user data', function (): void {
    $local = localMcpUser(); // EnsureLocalUser already creates "Local Workspace"
    $second = Team::factory()->create(['user_id' => $local->id, 'name' => 'Second Local']); // plus ONE more = 2
    $mine = Diagram::factory()->create(['team_id' => $second->id, 'name' => 'Mine Diagram']);

    $other = User::factory()->create();
    $foreignTeam = Team::factory()->create(['user_id' => $other->id, 'name' => 'Foreign Team']);
    Diagram::factory()->create(['team_id' => $foreignTeam->id, 'name' => 'Foreign Diagram']);

    $teams = mcpCallTool('list_teams')->assertOk()->json('result.content.0.text');
    expect($teams)->toContain('Local Workspace')
        ->and($teams)->toContain('Second Local')
        ->and($teams)->not->toContain('Foreign Team');

    $diagrams = mcpCallTool('list_diagrams')->assertOk()->json('result.content.0.text');
    expect($diagrams)->toContain('Mine Diagram')
        ->and($diagrams)->not->toContain('Foreign Diagram');
});

test('MCP04 tool acting on another user diagram returns an authorization error', function (): void {
    $other = User::factory()->create();
    $foreignTeam = Team::factory()->create(['user_id' => $other->id]);
    $diagram = Diagram::factory()->create(['team_id' => $foreignTeam->id, 'name' => 'Foreign Diagram']);

    $response = mcpCallTool('get_diagram', ['diagram_id' => $diagram->id])->assertOk();

    expect($response->json('result.isError'))->toBeTrue()
        ->and(json_encode($response->json()))->not->toContain('Foreign Diagram');
});

test('MCP05 a tool call creates the local user when none exists', function (): void {
    expect(User::count())->toBe(0);

    mcpCallTool('list_teams')->assertOk();

    $user = User::where('email', config('craftdb.local_user.email'))->first();
    expect($user)->not->toBeNull()
        ->and(json_encode(mcpCallTool('list_teams')->json()))->toContain('Local Workspace');
});

test('MCP04 add_relationship to a table of another user diagram is denied', function (): void {
    $local = localMcpUser();
    $mine = Diagram::factory()->create(['team_id' => $local->ownedTeams()->first()->id]);
    $other = User::factory()->create();
    $foreignTeam = Team::factory()->create(['user_id' => $other->id]);
    $foreign = Diagram::factory()->create(['team_id' => $foreignTeam->id]);
    $from = \App\Models\Table::factory()->create(['diagram_id' => $mine->id]);
    $to = \App\Models\Table::factory()->create(['diagram_id' => $foreign->id]);

    mcpCallTool('add_relationship', ['from_table_id' => $from->id, 'to_table_id' => $to->id, 'type' => '1:n'])
        ->assertOk()
        ->assertJsonPath('result.isError', true);

    expect(\App\Models\Relationship::count())->toBe(0);
});
