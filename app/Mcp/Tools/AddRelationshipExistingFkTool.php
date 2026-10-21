<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\AddRelationshipWithExistingForeignKey;
use App\Enums\RelationshipType;
use App\Mcp\McpAccess;
use App\Models\Table;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('add_relationship_existing_fk')]
#[Description('Create a relationship using existing columns as foreign keys. Use this when the FK column already exists in the table.')]
class AddRelationshipExistingFkTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'owner_table_id' => 'required|integer|exists:table,id',
            'owner_column_id' => 'required|integer|exists:column,id',
            'child_table_id' => 'required|integer|exists:table,id',
            'foreign_key_column_id' => 'required|integer|exists:column,id',
            'type' => 'nullable|string|in:1:1,1:n,n:1',
        ]);

        $type = isset($validated['type'])
            ? RelationshipType::from($validated['type'])
            : null;

        $ownerTable = Table::findOrFail($validated['owner_table_id']);
        $childTable = Table::findOrFail($validated['child_table_id']);
        if ($ownerTable->diagram_id !== $childTable->diagram_id) {
            return Response::error(__('The owner and child tables must belong to the same diagram.'));
        }

        McpAccess::assertDiagramAbilityForTable($ownerTable, 'update');

        $relationship = app(AddRelationshipWithExistingForeignKey::class)->exec([
            'ownerTableId' => $validated['owner_table_id'],
            'ownerColumnId' => $validated['owner_column_id'],
            'childTableId' => $validated['child_table_id'],
            'foreignKeyColumnId' => $validated['foreign_key_column_id'],
            'type' => $type,
        ]);

        return Response::text(json_encode([
            'id' => $relationship->id,
            'from_table_id' => $relationship->from_table_id,
            'from_column_id' => $relationship->from_column_id,
            'to_table_id' => $relationship->to_table_id,
            'to_column_id' => $relationship->to_column_id,
            'type' => $relationship->type->value,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'owner_table_id' => $schema->integer('The parent/owner table ID (has the primary key)'),
            'owner_column_id' => $schema->integer('The primary key column ID in the owner table'),
            'child_table_id' => $schema->integer('The child table ID (has the foreign key)'),
            'foreign_key_column_id' => $schema->integer('The foreign key column ID in the child table'),
            'type' => $schema->string('Relationship type: "1:1", "1:n", or "n:1" (default: "n:1")'),
        ];
    }
}
