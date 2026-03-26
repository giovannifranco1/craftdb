<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_diagram')]
#[Description('Create a new empty diagram. Returns the created diagram with its ID and slug. If team_id is unknown, call list_teams first so the user can choose which workspace to use.')]
class CreateDiagramTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_id' => 'required|integer|exists:team,id',
            'style' => 'required|in:neutral,colorful',
            'database' => 'required|in:mysql,postgresql,sqlite',
            'description' => 'nullable|string|max:255',
        ]);

        $team = Team::findOrFail($validated['team_id']);
        McpAccess::assertCanCreateDiagramOnTeam($request, $team);

        $slug = str()->slug($validated['name']).'-'.str()->random(10);

        $diagram = Diagram::create([
            'name' => $validated['name'],
            'team_id' => $validated['team_id'],
            'style' => $validated['style'],
            'database' => $validated['database'],
            'description' => $validated['description'] ?? null,
            'slug' => $slug,
        ]);

        return Response::text(json_encode([
            'id' => $diagram->id,
            'name' => $diagram->name,
            'slug' => $diagram->slug,
            'team_id' => $diagram->team_id,
            'style' => $diagram->style,
            'database' => $diagram->database,
            'description' => $diagram->description,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string('Diagram name'),
            'team_id' => $schema->integer('Team ID that owns the diagram'),
            'style' => $schema->string('Color style: "neutral" or "colorful"'),
            'database' => $schema->string('Database type: "mysql", "postgresql", or "sqlite"'),
            'description' => $schema->string('Optional diagram description'),
        ];
    }
}
