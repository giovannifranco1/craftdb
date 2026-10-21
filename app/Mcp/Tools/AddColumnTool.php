<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Table;
use App\Support\Helpers;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('add_column')]
#[Description('Add a column to a table with full control over name, type, and constraints.')]
class AddColumnTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'table_id' => 'required|integer|exists:table,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'is_primary' => 'nullable|boolean',
            'is_foreign' => 'nullable|boolean',
            'is_notnull' => 'nullable|boolean',
            'length' => 'nullable|integer|min:1',
            'default_value' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:500',
        ]);

        $table = Table::findOrFail($validated['table_id']);
        McpAccess::assertDiagramAbilityForTable($table, 'update');

        $maxOrder = $table->columns()->max('order');
        $newOrder = Helpers::calculateNextOrder($maxOrder);

        $column = $table->columns()->create([
            'name' => $validated['name'],
            'type' => strtolower($validated['type']),
            'is_primary' => $validated['is_primary'] ?? false,
            'is_foreign' => $validated['is_foreign'] ?? false,
            'is_notnull' => $validated['is_notnull'] ?? false,
            'order' => $newOrder,
            'length' => $validated['length'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        return Response::text(json_encode([
            'id' => $column->id,
            'table_id' => $column->table_id,
            'name' => $column->name,
            'type' => $column->type,
            'is_primary' => (bool) $column->is_primary,
            'is_foreign' => (bool) $column->is_foreign,
            'is_notnull' => (bool) $column->is_notnull,
            'order' => $column->order,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'table_id' => $schema->integer('The table ID to add the column to'),
            'name' => $schema->string('Column name (e.g., "email", "user_id")'),
            'type' => $schema->string('Column type: varchar, char, text, int, bigint, smallint, mediumint, tinyint, decimal, float, double, boolean, date, datetime, timestamp, time, year, json, uuid, enum'),
            'is_primary' => $schema->boolean('Is this a primary key column?'),
            'is_foreign' => $schema->boolean('Is this a foreign key column?'),
            'is_notnull' => $schema->boolean('Is this column NOT NULL?'),
            'length' => $schema->integer('Column length (e.g., 255 for varchar)'),
            'default_value' => $schema->string('Default value for the column'),
            'comment' => $schema->string('Column comment'),
        ];
    }
}
