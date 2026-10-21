<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Diagram;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_relationships')]
#[Description('List all relationships in a diagram with table and column names.')]
#[IsReadOnly(true)]
class ListRelationshipsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($diagram, 'view');

        $relationships = $diagram->relationships()
            ->with(['fromTable', 'fromColumn', 'toTable', 'toColumn'])
            ->get();

        $data = $relationships->map(fn ($rel) => [
            'id' => $rel->id,
            'from_table' => $rel->fromTable?->name,
            'from_column' => $rel->fromColumn?->name,
            'to_table' => $rel->toTable?->name,
            'to_column' => $rel->toColumn?->name,
            'type' => $rel->type->value,
            'label' => $rel->label,
        ])->all();

        return Response::text(json_encode($data, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to list relationships for'),
        ];
    }
}
