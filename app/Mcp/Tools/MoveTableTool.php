<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Table;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('move_table')]
#[Description('Move a table to a new position on the canvas.')]
class MoveTableTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'table_id' => 'required|integer|exists:table,id',
            'x' => 'required|integer',
            'y' => 'required|integer',
        ]);

        $table = Table::findOrFail($validated['table_id']);
        McpAccess::assertDiagramAbilityForTable($request, $table, 'update');

        $table->update(['x' => $validated['x'], 'y' => $validated['y']]);

        return Response::text(json_encode([
            'id' => $table->id,
            'name' => $table->name,
            'x' => $table->x,
            'y' => $table->y,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'table_id' => $schema->integer('The table ID to move'),
            'x' => $schema->integer('New X position on canvas'),
            'y' => $schema->integer('New Y position on canvas'),
        ];
    }
}
