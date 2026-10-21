<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\ExportDiagramToLaravelMigration;
use App\Mcp\McpAccess;
use App\Models\Diagram;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('export_laravel_migrations')]
#[Description('Export a diagram as Laravel migration PHP files. Returns an array of files with filename and content.')]
#[IsReadOnly(true)]
class ExportLaravelMigrationsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($diagram, 'view');

        $files = app(ExportDiagramToLaravelMigration::class)->exec($diagram);

        return Response::text(json_encode([
            'files_count' => count($files),
            'files' => $files,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID to export as Laravel migrations'),
        ];
    }
}
