<?php

namespace App\Mcp\Servers;

use App\Mcp\Server\CraftdbCallTool;
use App\Mcp\Tools\AddAreaTool;
use App\Mcp\Tools\AddColumnTool;
use App\Mcp\Tools\AddColumnsTool;
use App\Mcp\Tools\AddNoteTool;
use App\Mcp\Tools\AddRelationshipExistingFkTool;
use App\Mcp\Tools\AddRelationshipTool;
use App\Mcp\Tools\AddTableTool;
use App\Mcp\Tools\AddTablesTool;
use App\Mcp\Tools\CreateDiagramTool;
use App\Mcp\Tools\CreateSchemaTool;
use App\Mcp\Tools\DeleteAreaTool;
use App\Mcp\Tools\DeleteColumnTool;
use App\Mcp\Tools\DeleteDiagramTool;
use App\Mcp\Tools\DeleteNoteTool;
use App\Mcp\Tools\DeleteRelationshipTool;
use App\Mcp\Tools\DeleteTableTool;
use App\Mcp\Tools\ExportJsonTool;
use App\Mcp\Tools\ExportLaravelMigrationsTool;
use App\Mcp\Tools\ExportMermaidTool;
use App\Mcp\Tools\GetDiagramTool;
use App\Mcp\Tools\ListDiagramsTool;
use App\Mcp\Tools\ListTeamsTool;
use App\Mcp\Tools\ListRelationshipsTool;
use App\Mcp\Tools\MoveTableTool;
use App\Mcp\Tools\UpdateColumnTool;
use App\Mcp\Tools\UpdateDiagramTool;
use App\Mcp\Tools\UpdateTableTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('CraftDB')]
#[Version('0.1.0')]
#[Instructions('MCP server for CraftDB — a visual database diagram design platform. Use these tools to create, manage, and export database diagrams.

BEFORE creating or modifying any diagram, always call `list_teams` first. The result tells you:
- Which workspaces are available and their team_id.
- The number of diagrams in each workspace.

Prefer bulk tools to minimize round-trips: `create_schema` creates a full diagram in one call; `add_tables` adds multiple tables with columns to an existing diagram in one call; `add_columns` adds multiple columns to a table in one call.')]
class CraftdbServer extends Server
{
    /**
     * Cursor and other MCP clients often don't paginate tool listings in the UI.
     * Increase the default page size so all tools show up.
     */
    public int $defaultPaginationLength = 50;

    protected function boot(): void
    {
        $this->addMethod('tools/call', CraftdbCallTool::class);
    }

    protected array $tools = [
        // Diagram CRUD
        ListTeamsTool::class,
        ListDiagramsTool::class,
        GetDiagramTool::class,
        CreateDiagramTool::class,
        UpdateDiagramTool::class,
        DeleteDiagramTool::class,

        // Table CRUD
        AddTableTool::class,
        AddTablesTool::class,
        UpdateTableTool::class,
        DeleteTableTool::class,
        MoveTableTool::class,

        // Column CRUD
        AddColumnTool::class,
        AddColumnsTool::class,
        UpdateColumnTool::class,
        DeleteColumnTool::class,

        // Relationships
        AddRelationshipTool::class,
        AddRelationshipExistingFkTool::class,
        DeleteRelationshipTool::class,
        ListRelationshipsTool::class,

        // Areas & Notes
        AddAreaTool::class,
        DeleteAreaTool::class,
        AddNoteTool::class,
        DeleteNoteTool::class,

        // Export
        ExportJsonTool::class,
        ExportMermaidTool::class,
        ExportLaravelMigrationsTool::class,

        // Bulk
        CreateSchemaTool::class,
    ];
}
