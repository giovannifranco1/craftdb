<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Area;
use App\Models\Diagram;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('add_area')]
#[Description('Add a visual grouping area to a diagram. Areas help organize tables visually on the canvas.')]
class AddAreaTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
            'name' => 'required|string|max:255',
            'x' => 'required|integer',
            'y' => 'required|integer',
            'width' => 'required|integer|min:50',
            'height' => 'required|integer|min:50',
            'color' => 'nullable|string|max:20',
            'is_solid' => 'nullable|boolean',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($request, $diagram, 'update');

        $area = Area::create([
            'diagram_id' => $validated['diagram_id'],
            'name' => $validated['name'],
            'x' => $validated['x'],
            'y' => $validated['y'],
            'width' => $validated['width'],
            'height' => $validated['height'],
            'color' => $validated['color'] ?? '#808080',
            'is_solid' => $validated['is_solid'] ?? false,
        ]);

        return Response::text(json_encode([
            'id' => $area->id,
            'diagram_id' => $area->diagram_id,
            'name' => $area->name,
            'x' => $area->x,
            'y' => $area->y,
            'width' => $area->width,
            'height' => $area->height,
            'color' => $area->color,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID'),
            'name' => $schema->string('Area label/name'),
            'x' => $schema->integer('X position on canvas'),
            'y' => $schema->integer('Y position on canvas'),
            'width' => $schema->integer('Area width in pixels'),
            'height' => $schema->integer('Area height in pixels'),
            'color' => $schema->string('Hex color (default: #808080)'),
            'is_solid' => $schema->boolean('Whether the area has a solid background (default: false)'),
        ];
    }
}
