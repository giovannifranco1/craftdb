<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\AddRelationshipWithNewForeignKey;
use App\Mcp\McpAccess;
use App\Models\Diagram;
use App\Support\Helpers;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('add_tables')]
#[Description('Add multiple tables (with columns) to an existing diagram in a single call. Optionally create relationships between the new tables. Use this instead of calling add_table + add_column repeatedly.')]
class AddTablesTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
            'tables' => 'required|array|min:1',
            'tables.*.name' => 'required|string|max:255',
            'tables.*.x' => 'nullable|integer',
            'tables.*.y' => 'nullable|integer',
            'tables.*.color' => 'nullable|string',
            'tables.*.columns' => 'nullable|array',
            'tables.*.columns.*.name' => 'required|string|max:255',
            'tables.*.columns.*.type' => 'required|string|max:50',
            'tables.*.columns.*.is_primary' => 'nullable|boolean',
            'tables.*.columns.*.is_foreign' => 'nullable|boolean',
            'tables.*.columns.*.is_notnull' => 'nullable|boolean',
            'tables.*.columns.*.length' => 'nullable|integer|min:1',
            'tables.*.columns.*.default_value' => 'nullable|string|max:255',
            'tables.*.columns.*.comment' => 'nullable|string|max:500',
            'relationships' => 'nullable|array',
            'relationships.*.from_table' => 'required|string',
            'relationships.*.to_table' => 'required|string',
            'relationships.*.type' => 'required|string|in:1:1,1:n,n:1,n:n',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($diagram, 'update');

        $result = DB::transaction(function () use ($validated, $diagram) {
            $tableMap = [];
            $existingCount = $diagram->tables()->count();
            $tableIndex = $existingCount;

            foreach ($validated['tables'] as $tableData) {
                $tableIndex++;
                $hasPosition = isset($tableData['x']) && isset($tableData['y']);
                $position = $hasPosition
                    ? ['x' => $tableData['x'], 'y' => $tableData['y']]
                    : Helpers::calculateGridPosition($tableIndex);

                $table = $diagram->tables()->create([
                    'name' => $tableData['name'],
                    'x' => $position['x'],
                    'y' => $position['y'],
                    'color' => $tableData['color'] ?? Helpers::getRandomColor($diagram->style),
                ]);

                $tableMap[$tableData['name']] = $table;

                $order = 0;
                foreach ($tableData['columns'] ?? [] as $colData) {
                    $table->columns()->create([
                        'name' => $colData['name'],
                        'type' => strtolower($colData['type']),
                        'is_primary' => $colData['is_primary'] ?? false,
                        'is_foreign' => $colData['is_foreign'] ?? false,
                        'is_notnull' => $colData['is_notnull'] ?? false,
                        'order' => $order++,
                        'length' => $colData['length'] ?? null,
                        'default_value' => $colData['default_value'] ?? null,
                        'comment' => $colData['comment'] ?? null,
                    ]);
                }
            }

            $relationshipsCreated = 0;
            foreach ($validated['relationships'] ?? [] as $relData) {
                $fromTable = $tableMap[$relData['from_table']] ?? null;
                $toTable = $tableMap[$relData['to_table']] ?? null;

                if (! $fromTable || ! $toTable) {
                    continue;
                }

                app(AddRelationshipWithNewForeignKey::class)->exec([
                    'fromTableId' => $fromTable->id,
                    'toTableId' => $toTable->id,
                    'type' => $relData['type'],
                ]);

                $relationshipsCreated++;
            }

            return [
                'tables' => collect($tableMap)->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'x' => $t->x,
                    'y' => $t->y,
                    'columns_count' => $t->columns()->count(),
                ])->values(),
                'relationships_count' => $relationshipsCreated,
            ];
        });

        return Response::text(json_encode([
            'diagram_id' => $diagram->id,
            'tables_added' => count($result['tables']),
            'relationships_added' => $result['relationships_count'],
            'tables' => $result['tables'],
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to add tables to'),
            'tables' => $schema->array('Array of tables to add')->items(
                $schema->object([
                    'name' => $schema->string('Table name (e.g., "users", "posts")'),
                    'x' => $schema->integer('X position (optional, auto-calculated if omitted)'),
                    'y' => $schema->integer('Y position (optional, auto-calculated if omitted)'),
                    'color' => $schema->string('Hex color for the table header (optional)'),
                    'columns' => $schema->array('Array of columns (optional)')->items(
                        $schema->object([
                            'name' => $schema->string('Column name'),
                            'type' => $schema->string('Column type: bigint, int, varchar, text, boolean, datetime, timestamp, json, uuid, decimal, etc.'),
                            'is_primary' => $schema->boolean('Is primary key'),
                            'is_foreign' => $schema->boolean('Is foreign key'),
                            'is_notnull' => $schema->boolean('Is NOT NULL'),
                            'length' => $schema->integer('Column length (e.g., 255 for varchar)'),
                            'default_value' => $schema->string('Default value'),
                            'comment' => $schema->string('Column comment'),
                        ])
                    ),
                ])
            ),
            'relationships' => $schema->array('Relationships between the newly added tables (optional). FK columns are auto-created.')->items(
                $schema->object([
                    'from_table' => $schema->string('Source/parent table name (must match a table in the tables array)'),
                    'to_table' => $schema->string('Target/child table name (must match a table in the tables array)'),
                    'type' => $schema->string('Relationship type: "1:1", "1:n", "n:1", or "n:n"'),
                ])
            ),
        ];
    }
}
