<?php

use App\Mcp\Tools\ListDiagramsTool;
use App\Models\Diagram;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Laravel\Cashier\Subscription;
use Laravel\Mcp\Request;

beforeEach(function (): void {
    Artisan::call('passport:client', [
        '--personal' => true,
        '--name' => 'Test MCP',
        '--provider' => 'users',
    ]);
});

test('gate createDiagramOnTeam allows team owner', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    expect(Gate::forUser($user)->allows('createDiagramOnTeam', $team))->toBeTrue();
});

test('mcp list_diagrams returns no diagrams for free api user on own team', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    Diagram::factory()->create(['team_id' => $team->id]);

    $this->actingAs($owner);

    $response = (new ListDiagramsTool)->handle(new Request([]));
    $json = json_decode((string) $response->content(), true);

    expect($json)->toBeArray()->and($json)->toHaveCount(0);
});

test('mcp list_diagrams returns diagrams when owner has pro subscription', function (): void {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    Diagram::factory()->create(['team_id' => $team->id]);

    Subscription::factory()
        ->for($owner)
        ->withPrice(config('billing.plans.pro_usd.price_id'))
        ->create([
            'type' => config('billing.subscription_name', 'default'),
        ]);

    User::clearSubscriptionCaches();

    $this->actingAs($owner);

    $response = (new ListDiagramsTool)->handle(new Request([]));
    $json = json_decode((string) $response->content(), true);

    expect($json)->toHaveCount(1);
});

test('mcp endpoint rejects unauthenticated request', function (): void {
    $this->postJson('/mcp/craftdb', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => [],
            'clientInfo' => ['name' => 'test', 'version' => '1.0'],
        ],
    ])->assertUnauthorized();
});
