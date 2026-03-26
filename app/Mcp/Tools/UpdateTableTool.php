<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\UpdateTable;
use App\Mcp\McpAccess;
use App\Models\Table;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_table')]
#[Description('Update a table name, color, or position.')]
class UpdateTableTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'table_id' => 'required|integer|exists:table,id',
            'name' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'x' => 'nullable|integer',
            'y' => 'nullable|integer',
        ]);

        $table = Table::findOrFail($validated['table_id']);
        McpAccess::assertDiagramAbilityForTable($request, $table, 'update');

        $data = array_filter([
            'name' => $validated['name'] ?? null,
            'color' => $validated['color'] ?? null,
            'x' => $validated['x'] ?? null,
            'y' => $validated['y'] ?? null,
        ], fn ($v) => $v !== null);

        $table = app(UpdateTable::class)->exec($table, $data);

        return Response::text(json_encode([
            'id' => $table->id,
            'name' => $table->name,
            'x' => $table->x,
            'y' => $table->y,
            'color' => $table->color,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'table_id' => $schema->integer('The table ID to update'),
            'name' => $schema->string('New table name'),
            'color' => $schema->string('New hex color'),
            'x' => $schema->integer('New X position'),
            'y' => $schema->integer('New Y position'),
        ];
    }
}
