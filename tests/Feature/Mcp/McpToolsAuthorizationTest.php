<?php

use App\Mcp\Servers\CraftdbServer;
use App\Mcp\Tools\GetDiagramTool;
use App\Mcp\Tools\ListDiagramsTool;
use App\Mcp\Tools\ListTeamsTool;
use App\Models\Diagram;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Instructions;

beforeEach(function (): void {
    $this->owner = User::factory()->create();
    $this->team = Team::factory()->create(['user_id' => $this->owner->id]);
});

test('gate createDiagramOnTeam allows team owner', function (): void {
    expect(Gate::forUser($this->owner)->allows('createDiagramOnTeam', $this->team))->toBeTrue();
});

test('FREE20 mcp list_diagrams returns diagrams for the local user', function (): void {
    Diagram::factory()->count(2)->create(['team_id' => $this->team->id]);

    $this->actingAs($this->owner);

    $response = (new ListDiagramsTool)->handle(new Request([]));
    $json = json_decode((string) $response->content(), true);

    expect($json)->toBeArray()->and($json)->toHaveCount(2);
});

test('FREE19 mcp get_diagram on own team diagram works for the local user', function (): void {
    $diagram = Diagram::factory()->create(['team_id' => $this->team->id]);

    $this->actingAs($this->owner);

    $response = (new GetDiagramTool)->handle(new Request(['diagram_id' => $diagram->id]));

    expect($response->isError())->toBeFalse();
    expect((string) $response->content())->toContain($diagram->name);
});

test('FREE21 mcp list_teams and server instructions mention no plans', function (): void {
    $this->actingAs($this->owner);

    $text = (string) (new ListTeamsTool)->handle(new Request([]))->content();
    $instructions = (new ReflectionClass(CraftdbServer::class))->getAttributes(Instructions::class)[0]->newInstance()->value;
    $description = (new ReflectionClass(ListTeamsTool::class))->getAttributes(Description::class)[0]->newInstance()->value;

    foreach ([$text, $instructions, $description] as $content) {
        foreach (['Free', 'Pro', 'Team plan', 'upgrade', '/settings/billing'] as $word) {
            expect($content)->not->toContain($word);
        }
    }
    expect($text)->toContain('team_id');
});
