<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\DeleteDiagram;
use App\Mcp\McpAccess;
use App\Models\Diagram;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_diagram')]
#[Description('Delete a diagram and all its contents (tables, columns, relationships, areas, notes). This action is irreversible.')]
#[IsDestructive(true)]
class DeleteDiagramTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($request, $diagram, 'delete');

        $name = $diagram->name;

        app(DeleteDiagram::class)->exec($diagram);

        return Response::text(json_encode([
            'success' => true,
            'message' => "Diagram '{$name}' deleted successfully.",
        ]));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to delete'),
        ];
    }
}
