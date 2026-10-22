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

#[Name('get_diagram')]
#[Description('Get a complete diagram with all tables, columns, relationships, areas, and notes. Provide either diagram_id or slug.')]
#[IsReadOnly(true)]
class GetDiagramTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'nullable|integer',
            'slug' => 'nullable|string',
        ]);

        if (empty($validated['diagram_id']) && empty($validated['slug'])) {
            return Response::error('Provide either diagram_id or slug.');
        }

        $diagram = empty($validated['diagram_id'])
            ? Diagram::where('slug', $validated['slug'])->firstOrFail()
            : Diagram::findOrFail($validated['diagram_id']);

        McpAccess::assertDiagramAbility($diagram, 'view');

        $diagram->load(['tables.columns', 'areas', 'notes']);

        $relationships = $diagram->relationships()
            ->with(['fromTable', 'fromColumn', 'toTable', 'toColumn'])
            ->get();

        $result = [
            'diagram' => [
                'id' => $diagram->id,
                'team_id' => $diagram->team_id,
                'name' => $diagram->name,
                'slug' => $diagram->slug,
                'style' => $diagram->style,
                'database' => $diagram->database,
                'description' => $diagram->description,
            ],
            'tables' => $diagram->tables->map(fn ($table) => [
                'id' => $table->id,
                'name' => $table->name,
                'x' => $table->x,
                'y' => $table->y,
                'color' => $table->color,
                'is_pivot' => (bool) $table->is_pivot,
                'columns' => $table->columns->sortBy('order')->values()->map(fn ($col) => [
                    'id' => $col->id,
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
            ])->values()->all(),
            'relationships' => $relationships->map(fn ($rel) => [
                'id' => $rel->id,
                'from_table' => $rel->fromTable?->name,
                'from_column' => $rel->fromColumn?->name,
                'to_table' => $rel->toTable?->name,
                'to_column' => $rel->toColumn?->name,
                'type' => $rel->type->value,
                'label' => $rel->label,
            ])->all(),
            'areas' => $diagram->areas->map(fn ($area) => [
                'id' => $area->id,
                'name' => $area->name,
                'x' => $area->x,
                'y' => $area->y,
                'width' => $area->width,
                'height' => $area->height,
                'color' => $area->color,
            ])->all(),
            'notes' => $diagram->notes->map(fn ($note) => [
                'id' => $note->id,
                'name' => $note->name,
                'content' => $note->content,
                'x' => $note->x,
                'y' => $note->y,
                'color' => $note->color,
            ])->all(),
        ];

        return Response::text(json_encode($result, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID'),
            'slug' => $schema->string('The diagram slug (alternative to diagram_id)'),
        ];
    }
}
