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

#[Name('add_note')]
#[Description('Add a sticky note to a diagram. Notes can contain free-text annotations.')]
class AddNoteTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'diagram_id' => 'required|integer|exists:diagram,id',
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'x' => 'required|integer',
            'y' => 'required|integer',
            'color' => 'nullable|string|max:20',
        ]);

        $diagram = Diagram::findOrFail($validated['diagram_id']);
        McpAccess::assertDiagramAbility($request, $diagram, 'update');

        $note = Note::create([
            'diagram_id' => $validated['diagram_id'],
            'name' => $validated['name'],
            'content' => $validated['content'] ?? null,
            'x' => $validated['x'],
            'y' => $validated['y'],
            'color' => $validated['color'] ?? Note::DEFAULT_COLOR,
        ]);

        return Response::text(json_encode([
            'id' => $note->id,
            'diagram_id' => $note->diagram_id,
            'name' => $note->name,
            'content' => $note->content,
            'x' => $note->x,
            'y' => $note->y,
            'color' => $note->color,
        ], JSON_PRETTY_PRINT));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'diagram_id' => $schema->integer('The diagram ID'),
            'name' => $schema->string('Note title'),
            'content' => $schema->string('Note content/body text'),
            'x' => $schema->integer('X position on canvas'),
            'y' => $schema->integer('Y position on canvas'),
            'color' => $schema->string('Hex color (default: #fef08a / yellow)'),
        ];
    }
}
