<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\DeleteColumnToTable;
use App\Mcp\McpAccess;
use App\Models\Column;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_column')]
#[Description('Delete a column and its associated relationships.')]
#[IsDestructive(true)]
class DeleteColumnTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'column_id' => 'required|integer|exists:column,id',
        ]);

        $column = Column::findOrFail($validated['column_id']);
        $column->loadMissing('table');
        McpAccess::assertDiagramAbilityForTable($request, $column->table, 'update');

        $name = $column->name;

        app(DeleteColumnToTable::class)->exec($column);

        return Response::text(json_encode([
            'success' => true,
            'message' => "Column '{$name}' deleted successfully.",
        ]));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'column_id' => $schema->integer('The column ID to delete'),
        ];
    }
}
