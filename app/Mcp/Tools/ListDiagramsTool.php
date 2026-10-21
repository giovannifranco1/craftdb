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

#[Name('list_diagrams')]
#[Description('List all diagrams, optionally filtered by team_id.')]
#[IsReadOnly(true)]
class ListDiagramsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $query = Diagram::query()->withCount('tables');

        $teamIds = McpAccess::teamIdsForUser();
        $query->whereIn('team_id', $teamIds);

        if ($teamId = $request->get('team_id')) {
            $query->where('team_id', $teamId);
        }

        $diagrams = $query->orderByDesc('updated_at')->get([
            'id', 'team_id', 'name', 'slug', 'style', 'database', 'description', 'created_at', 'updated_at',
        ]);

        return Response::text(json_encode($diagrams, JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'team_id' => $schema->integer('Filter by team ID (optional)'),
        ];
    }
}
