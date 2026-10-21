<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Column;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('update_column')]
#[Description('Update column properties such as name, type, constraints, length, default value, or comment.')]
class UpdateColumnTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'column_id' => 'required|integer|exists:column,id',
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'is_foreign' => 'nullable|boolean',
            'is_notnull' => 'nullable|boolean',
            'length' => 'nullable|integer|min:1',
            'default_value' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:500',
        ]);

        $column = Column::findOrFail($validated['column_id']);
        $column->loadMissing('table');
        McpAccess::assertDiagramAbilityForTable($column->table, 'update');

        $data = collect($validated)
            ->except('column_id')
            ->filter(fn ($v) => $v !== null)
            ->when(isset($validated['type']), fn ($c) => $c->put('type', strtolower($validated['type'])))
            ->all();

        $column->update($data);

        return Response::text(json_encode([
            'id' => $column->id,
            'table_id' => $column->table_id,
            'name' => $column->name,
            'type' => $column->type,
            'is_primary' => (bool) $column->is_primary,
            'is_foreign' => (bool) $column->is_foreign,
            'is_notnull' => (bool) $column->is_notnull,
            'length' => $column->length,
            'default_value' => $column->default_value,
            'comment' => $column->comment,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'column_id' => $schema->integer('The column ID to update'),
            'name' => $schema->string('New column name'),
            'type' => $schema->string('New column type'),
            'is_primary' => $schema->boolean('Is primary key'),
            'is_foreign' => $schema->boolean('Is foreign key'),
            'is_notnull' => $schema->boolean('Is NOT NULL'),
            'length' => $schema->integer('Column length'),
            'default_value' => $schema->string('Default value'),
            'comment' => $schema->string('Column comment'),
        ];
    }
}
