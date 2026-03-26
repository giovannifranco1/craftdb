<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Diagram;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('export_json')]
#[Description('Export a diagram as CraftDB JSON format with all tables, columns, and relationships.')]
#[IsReadOnly(true)]
class ExportJsonTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($request, $diagram, 'view');

        $diagram->loadMissing('tables.columns');

        $relationships = $diagram->relationships()
            ->with(['fromTable', 'fromColumn', 'toTable', 'toColumn'])
            ->get();

        $result = [
            'craftdb_format' => 'full',
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'diagram' => [
                'name' => $diagram->name,
                'style' => $diagram->style,
                'database' => $diagram->database,
                'description' => $diagram->description,
            ],
            'tables' => $diagram->tables->map(fn ($table) => [
                'name' => $table->name,
                'columns' => $table->columns->sortBy('order')->values()->map(fn ($col) => [
                    'name' => $col->name,
                    'type' => $col->type,
                    'is_primary' => (bool) $col->is_primary,
                    'is_foreign' => (bool) $col->is_foreign,
                    'is_notnull' => (bool) $col->is_notnull,
                    'order' => $col->order,
                    'length' => $col->length,
                    'default_value' => $col->default_value,
                    'comment' => $col->comment,
                ])->all(),
            ])->all(),
            'relationships' => $relationships->map(fn ($rel) => [
                'from_table' => $rel->fromTable?->name,
                'from_column' => $rel->fromColumn?->name,
                'to_table' => $rel->toTable?->name,
                'to_column' => $rel->toColumn?->name,
                'type' => $rel->type->value,
                'label' => $rel->label,
            ])->all(),
        ];

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to export'),
        ];
    }
}
