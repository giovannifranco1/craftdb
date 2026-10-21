<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Diagram;
use App\Support\Helpers;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('add_table')]
#[Description('Add a new table to a diagram. Optionally specify position (x, y) and color. If not provided, position is auto-calculated and color is random from the diagram style palette.')]
class AddTableTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
            'name' => 'required|string|max:255',
            'x' => 'nullable|integer',
            'y' => 'nullable|integer',
            'color' => 'nullable|string',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($diagram, 'update');

        $tableCount = $diagram->tables()->count();
        $position = (isset($validated['x']) && isset($validated['y']))
            ? ['x' => $validated['x'], 'y' => $validated['y']]
            : Helpers::calculateGridPosition($tableCount + 1);

        $table = $diagram->tables()->create([
            'name' => $validated['name'],
            'x' => $position['x'],
            'y' => $position['y'],
            'color' => $validated['color'] ?? Helpers::getRandomColor($diagram->style),
        ]);

        return Response::text(json_encode([
            'id' => $table->id,
            'diagram_id' => $table->diagram_id,
            'name' => $table->name,
            'x' => $table->x,
            'y' => $table->y,
            'color' => $table->color,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to add the table to'),
            'name' => $schema->string('Table name (e.g., "users", "posts")'),
            'x' => $schema->integer('X position on canvas (optional, auto-calculated if omitted)'),
            'y' => $schema->integer('Y position on canvas (optional, auto-calculated if omitted)'),
            'color' => $schema->string('Hex color for the table header (optional, random from palette if omitted)'),
        ];
    }
}
