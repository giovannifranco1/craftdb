<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Relationship;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_relationship')]
#[Description('Delete a relationship between two tables.')]
#[IsDestructive(true)]
class DeleteRelationshipTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'relationship_id' => 'required|integer|exists:relationship,id',
        ]);

        $relationship = Relationship::with('fromTable')->findOrFail($validated['relationship_id']);
        if ($relationship->fromTable) {
            McpAccess::assertDiagramAbilityForTable($request, $relationship->fromTable, 'update');
        }

        $relationship->delete();

        return Response::text(json_encode([
            'success' => true,
            'message' => 'Relationship deleted successfully.',
        ]));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'relationship_id' => $schema->integer('The relationship ID to delete'),
        ];
    }
}
