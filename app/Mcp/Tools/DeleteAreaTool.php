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
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_area')]
#[Description('Delete a visual grouping area from a diagram.')]
#[IsDestructive(true)]
class DeleteAreaTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'area_id' => 'required|integer|exists:area,id',
        ]);

        $area = Area::findOrFail($validated['area_id']);
        $diagram = Diagram::findOrFail($area->diagram_id);
        McpAccess::assertDiagramAbility($diagram, 'update');

        $name = $area->name;
        $area->delete();

        return Response::text(json_encode([
            'success' => true,
            'message' => "Area '{$name}' deleted successfully.",
        ]));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'area_id' => $schema->integer('The area ID to delete'),
        ];
    }
}
