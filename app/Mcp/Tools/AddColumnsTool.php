<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Table;
use App\Support\Helpers;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('add_columns')]
#[Description('Add multiple columns to a table in a single call. Use this instead of calling add_column repeatedly.')]
class AddColumnsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'table_id' => 'required|integer|exists:table,id',
            'columns' => 'required|array|min:1',
            'columns.*.name' => 'required|string|max:255',
            'columns.*.type' => 'required|string|max:50',
            'columns.*.is_primary' => 'nullable|boolean',
            'columns.*.is_foreign' => 'nullable|boolean',
            'columns.*.is_notnull' => 'nullable|boolean',
            'columns.*.length' => 'nullable|integer|min:1',
            'columns.*.default_value' => 'nullable|string|max:255',
            'columns.*.comment' => 'nullable|string|max:500',
        ]);

        $table = Table::findOrFail($validated['table_id']);
        McpAccess::assertDiagramAbilityForTable($request, $table, 'update');

        $created = DB::transaction(function () use ($validated, $table) {
            $maxOrder = $table->columns()->max('order') ?? -1;
            $results = [];

            foreach ($validated['columns'] as $colData) {
                $maxOrder = Helpers::calculateNextOrder($maxOrder);

                $column = $table->columns()->create([
                    'name' => $colData['name'],
                    'type' => strtolower($colData['type']),
                    'is_primary' => $colData['is_primary'] ?? false,
                    'is_foreign' => $colData['is_foreign'] ?? false,
                    'is_notnull' => $colData['is_notnull'] ?? false,
                    'order' => $maxOrder,
                    'length' => $colData['length'] ?? null,
                    'default_value' => $colData['default_value'] ?? null,
                    'comment' => $colData['comment'] ?? null,
                ]);

                $results[] = [
                    'id' => $column->id,
                    'name' => $column->name,
                    'type' => $column->type,
                    'is_primary' => (bool) $column->is_primary,
                    'is_foreign' => (bool) $column->is_foreign,
                    'is_notnull' => (bool) $column->is_notnull,
                    'order' => $column->order,
                ];
            }

            return $results;
        });

        return Response::text(json_encode([
            'table_id' => $table->id,
            'columns_added' => count($created),
            'columns' => $created,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'table_id' => $schema->integer('The table ID to add columns to'),
            'columns' => $schema->array('Array of columns to add')->items(
                $schema->object([
                    'name' => $schema->string('Column name (e.g., "email", "user_id")'),
                    'type' => $schema->string('Column type: varchar, char, text, int, bigint, smallint, mediumint, tinyint, decimal, float, double, boolean, date, datetime, timestamp, time, year, json, uuid, enum'),
                    'is_primary' => $schema->boolean('Is this a primary key column?'),
                    'is_foreign' => $schema->boolean('Is this a foreign key column?'),
                    'is_notnull' => $schema->boolean('Is this column NOT NULL?'),
                    'length' => $schema->integer('Column length (e.g., 255 for varchar)'),
                    'default_value' => $schema->string('Default value for the column'),
                    'comment' => $schema->string('Column comment'),
                ])
            ),
        ];
    }
}
