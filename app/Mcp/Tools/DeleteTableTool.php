<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\DeleteTable;
use App\Mcp\McpAccess;
use App\Models\Table;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_table')]
#[Description('Delete a table and all its columns and relationships.')]
#[IsDestructive(true)]
class DeleteTableTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'table_id' => 'required|integer|exists:table,id',
        ]);

        $table = Table::findOrFail($validated['table_id']);
        McpAccess::assertDiagramAbilityForTable($request, $table, 'update');

        $name = $table->name;

        app(DeleteTable::class)->exec($table);

        return Response::text(json_encode([
            'success' => true,
            'message' => "Table '{$name}' deleted successfully.",
        ]));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'table_id' => $schema->integer('The table ID to delete'),
        ];
    }
}
