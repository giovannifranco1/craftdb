<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\UpdateDiagram;
use App\Mcp\McpAccess;
use App\Models\Diagram;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_diagram')]
#[Description('Update a diagram name, description, style, or database. WARNING: Changing style or database will delete all tables, columns, and relationships.')]
class UpdateDiagramTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
            'name' => 'required|string|max:255',
            'style' => 'required|in:neutral,colorful',
            'database' => 'required|in:mysql,postgresql,sqlite',
            'description' => 'nullable|string|max:255',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($diagram, 'update');

        $diagram = app(UpdateDiagram::class)->exec($diagram, [
            'name' => $validated['name'],
            'style' => $validated['style'],
            'database' => $validated['database'],
            'description' => $validated['description'] ?? null,
        ]);

        return Response::text(json_encode([
            'id' => $diagram->id,
            'name' => $diagram->name,
            'style' => $diagram->style,
            'database' => $diagram->database,
            'description' => $diagram->description,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to update'),
            'name' => $schema->string('New diagram name'),
            'style' => $schema->string('Color style: "neutral" or "colorful"'),
            'database' => $schema->string('Database type: "mysql", "postgresql", or "sqlite"'),
            'description' => $schema->string('Optional diagram description'),
        ];
    }
}
