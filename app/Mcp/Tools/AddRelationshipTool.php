<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\AddRelationshipWithNewForeignKey;
use App\Mcp\McpAccess;
use App\Models\Table;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('add_relationship')]
#[Description('Create a relationship between two tables with auto-creation of foreign key columns. Supports 1:1 (one-to-one), 1:n (one-to-many), n:1 (many-to-one/belongs-to), and n:n (many-to-many, creates a pivot table automatically).')]
class AddRelationshipTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'from_table_id' => 'required|integer|exists:table,id',
            'to_table_id' => 'required|integer|exists:table,id',
            'type' => 'required|string|in:1:1,1:n,n:1,n:n',
        ]);

        $from = Table::findOrFail($validated['from_table_id']);
        $to = Table::findOrFail($validated['to_table_id']);
        if ($from->diagram_id !== $to->diagram_id) {
            return Response::error(__('The two tables must belong to the same diagram.'));
        }

        McpAccess::assertDiagramAbilityForTable($request, $from, 'update');

        $result = app(AddRelationshipWithNewForeignKey::class)->exec([
            'fromTableId' => $validated['from_table_id'],
            'toTableId' => $validated['to_table_id'],
            'type' => $validated['type'],
        ]);

        if ($result instanceof Collection) {
            $data = $result->map(fn ($rel) => [
                'id' => $rel->id,
                'from_table_id' => $rel->from_table_id,
                'to_table_id' => $rel->to_table_id,
                'type' => $rel->type->value,
            ])->all();
        } else {
            $data = [
                'id' => $result->id,
                'from_table_id' => $result->from_table_id,
                'to_table_id' => $result->to_table_id,
                'type' => $result->type->value,
            ];
        }

        return Response::text(json_encode($data, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'from_table_id' => $schema->integer('The source/parent table ID'),
            'to_table_id' => $schema->integer('The target/child table ID'),
            'type' => $schema->string('Relationship type: "1:1", "1:n", "n:1", or "n:n"'),
        ];
    }
}
