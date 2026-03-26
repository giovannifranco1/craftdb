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

#[Name('export_mermaid_erd')]
#[Description('Export a diagram as Mermaid ERD syntax. The output can be rendered in any Mermaid-compatible tool (GitHub, VS Code, mermaid.live).')]
#[IsReadOnly(true)]
class ExportMermaidTool extends Tool
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

        $lines = ['erDiagram'];

        // Tables with columns
        foreach ($diagram->tables as $table) {
            $lines[] = "    {$table->name} {";
            foreach ($table->columns->sortBy('order') as $col) {
                $constraint = '';
                if ($col->is_primary) {
                    $constraint = ' PK';
                } elseif ($col->is_foreign) {
                    $constraint = ' FK';
                }

                $lines[] = "        {$col->type} {$col->name}{$constraint}";
            }
            $lines[] = '    }';
        }

        // Relationships
        foreach ($relationships as $rel) {
            $from = $rel->fromTable?->name;
            $to = $rel->toTable?->name;

            if (! $from || ! $to) {
                continue;
            }

            $arrow = match ($rel->type->value) {
                '1:1' => '||--||',
                '1:n' => '||--o{',
                'n:1' => '}o--||',
                default => '||--o{',
            };

            $label = $rel->label ?: 'references';
            $lines[] = "    {$from} {$arrow} {$to} : \"{$label}\"";
        }

        $mermaid = implode("\n", $lines);

        return Response::text(json_encode([
            'mermaid' => $mermaid,
            'tables_count' => $diagram->tables->count(),
            'relationships_count' => $relationships->count(),
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to export as Mermaid ERD'),
        ];
    }
}
