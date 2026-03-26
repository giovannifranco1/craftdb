<?php

namespace App\Mcp\Tools;

use App\Mcp\McpAccess;
use App\Models\Diagram;
use App\Models\Note;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_note')]
#[Description('Delete a note from a diagram.')]
#[IsDestructive(true)]
class DeleteNoteTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'note_id' => 'required|integer|exists:note,id',
        ]);

        $note = Note::findOrFail($validated['note_id']);
        $diagram = Diagram::findOrFail($note->diagram_id);
        McpAccess::assertDiagramAbility($request, $diagram, 'update');

        $name = $note->name;
        $note->delete();

        return Response::text(json_encode([
            'success' => true,
            'message' => "Note '{$name}' deleted successfully.",
        ]));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'note_id' => $schema->integer('The note ID to delete'),
        ];
    }
}
