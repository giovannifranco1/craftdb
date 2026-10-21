<?php

namespace App\Mcp\Tools;

use App\Actions\Diagram\AddRelationshipWithNewForeignKey;
use App\Mcp\McpAccess;
use App\Models\Area;
use App\Models\Diagram;
use App\Models\Team\Team;
use App\Support\Helpers;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('create_schema')]
#[Description('Create a complete diagram with tables, columns, relationships and optional areas (visual groups) in one call. This is the most powerful tool — use it to generate an entire database schema at once. Tables are auto-positioned in a grid if positions are not specified. Foreign key columns are auto-created for relationships. Use areas to visually group related tables (e.g. "Auth", "Billing", "Orders"). If team_id is unknown, call list_teams first so the user can choose which workspace to use.')]
class CreateSchemaTool extends Tool
{
    // Layout constants
    private const TABLE_W         = 280;
    private const TABLE_H_BASE    = 100;
    private const TABLE_H_PER_COL = 28;
    private const TABLE_GAP_X     = 60;
    private const TABLE_GAP_Y     = 60;
    private const MAX_PER_ROW     = 3;
    private const AREA_PAD_X      = 40;
    private const AREA_PAD_TOP    = 70;
    private const AREA_PAD_BOTTOM = 40;
    private const AREA_GAP        = 100;

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'name'                              => 'required|string|max:255',
            'team_id'                           => 'required|integer|exists:team,id',
            'style'                             => 'nullable|in:neutral,colorful',
            'database'                          => 'nullable|in:mysql,postgresql,sqlite',
            'description'                       => 'nullable|string|max:255',
            'tables'                            => 'required|array|min:1',
            'tables.*.name'                     => 'required|string|max:255',
            'tables.*.x'                        => 'nullable|integer',
            'tables.*.y'                        => 'nullable|integer',
            'tables.*.columns'                  => 'required|array|min:1',
            'tables.*.columns.*.name'           => 'required|string|max:255',
            'tables.*.columns.*.type'           => 'required|string|max:50',
            'tables.*.columns.*.is_primary'     => 'nullable|boolean',
            'tables.*.columns.*.is_foreign'     => 'nullable|boolean',
            'tables.*.columns.*.is_notnull'     => 'nullable|boolean',
            'tables.*.columns.*.length'         => 'nullable|integer|min:1',
            'tables.*.columns.*.default_value'  => 'nullable|string|max:255',
            'tables.*.columns.*.comment'        => 'nullable|string|max:500',
            'relationships'                     => 'nullable|array',
            'relationships.*.from_table'        => 'required|string',
            'relationships.*.to_table'          => 'required|string',
            'relationships.*.type'              => 'required|string|in:1:1,1:n,n:1,n:n',
            'areas'                             => 'nullable|array',
            'areas.*.name'                      => 'required|string|max:255',
            'areas.*.color'                     => 'nullable|string|max:20',
            'areas.*.tables'                    => 'required|array|min:1',
            'areas.*.tables.*'                  => 'required|string',
        ]);

        $team = Team::findOrFail($validated['team_id']);
        McpAccess::assertCanCreateDiagramOnTeam($team);

        $style    = $validated['style'] ?? 'colorful';
        $database = $validated['database'] ?? 'mysql';

        $result = DB::transaction(function () use ($validated, $style, $database) {
            $slug = str()->slug($validated['name']).'-'.str()->random(10);

            $diagram = Diagram::create([
                'name'        => $validated['name'],
                'team_id'     => $validated['team_id'],
                'style'       => $style,
                'database'    => $database,
                'description' => $validated['description'] ?? null,
                'slug'        => $slug,
            ]);

            // Build a lookup: table name => column count (for height estimation)
            $colCounts = collect($validated['tables'])
                ->mapWithKeys(fn ($t) => [$t['name'] => count($t['columns'])]);

            // Resolve which tables belong to each area (and which are free)
            $areasDef    = $validated['areas'] ?? [];
            $assignedTo  = []; // table name => area index
            foreach ($areasDef as $aIdx => $areaDef) {
                foreach ($areaDef['tables'] as $tName) {
                    $assignedTo[$tName] = $aIdx;
                }
            }

            // Calculate per-area canvas layout
            $areaLayouts = $this->calculateAreaLayouts($areasDef, $colCounts->all());

            // Calculate starting Y for free (unassigned) tables — below all areas
            $maxAreaBottom = 0;
            foreach ($areaLayouts as $layout) {
                $bottom = $layout['y'] + $layout['height'] + self::AREA_GAP;
                if ($bottom > $maxAreaBottom) {
                    $maxAreaBottom = $bottom;
                }
            }
            $freeTableIndex = 0;

            // 2. Create tables with columns
            $tableMap = [];

            foreach ($validated['tables'] as $tableData) {
                $tName = $tableData['name'];

                if (isset($tableData['x'], $tableData['y'])) {
                    $position = ['x' => $tableData['x'], 'y' => $tableData['y']];
                } elseif (isset($assignedTo[$tName])) {
                    $aIdx     = $assignedTo[$tName];
                    $position = $areaLayouts[$aIdx]['tablePositions'][$tName];
                } else {
                    $freeTableIndex++;
                    $pos      = Helpers::calculateGridPosition($freeTableIndex);
                    $position = ['x' => $pos['x'], 'y' => $pos['y'] + $maxAreaBottom];
                }

                $table = $diagram->tables()->create([
                    'name'  => $tName,
                    'x'     => $position['x'],
                    'y'     => $position['y'],
                    'color' => Helpers::getRandomColor($style),
                ]);

                $tableMap[$tName] = $table;

                $order = 0;
                foreach ($tableData['columns'] as $colData) {
                    $table->columns()->create([
                        'name'          => $colData['name'],
                        'type'          => strtolower($colData['type']),
                        'is_primary'    => $colData['is_primary'] ?? false,
                        'is_foreign'    => $colData['is_foreign'] ?? false,
                        'is_notnull'    => $colData['is_notnull'] ?? false,
                        'order'         => $order++,
                        'length'        => $colData['length'] ?? null,
                        'default_value' => $colData['default_value'] ?? null,
                        'comment'       => $colData['comment'] ?? null,
                    ]);
                }
            }

            // 3. Create relationships
            $relationshipsCreated = 0;
            foreach ($validated['relationships'] ?? [] as $relData) {
                $fromTable = $tableMap[$relData['from_table']] ?? null;
                $toTable   = $tableMap[$relData['to_table']] ?? null;

                if (! $fromTable || ! $toTable) {
                    continue;
                }

                app(AddRelationshipWithNewForeignKey::class)->exec([
                    'fromTableId' => $fromTable->id,
                    'toTableId'   => $toTable->id,
                    'type'        => $relData['type'],
                ]);

                $relationshipsCreated++;
            }

            // 4. Create areas
            $areasCreated = 0;
            foreach ($areasDef as $aIdx => $areaDef) {
                $layout = $areaLayouts[$aIdx];

                Area::create([
                    'diagram_id' => $diagram->id,
                    'name'       => $areaDef['name'],
                    'x'          => $layout['x'],
                    'y'          => $layout['y'],
                    'width'      => $layout['width'],
                    'height'     => $layout['height'],
                    'color'      => $areaDef['color'] ?? '#808080',
                    'is_solid'   => false,
                ]);

                $areasCreated++;
            }

            return [
                'diagram'               => $diagram,
                'tables_count'          => count($validated['tables']),
                'relationships_count'   => $relationshipsCreated,
                'areas_count'           => $areasCreated,
            ];
        });

        // refresh() clears all cached relations so we get a clean load from DB,
        // including FK columns that AddRelationshipWithNewForeignKey added after table creation.
        $diagram = $result['diagram']->refresh();
        $diagram->load('tables.columns');

        $relationships = $diagram->relationships()
            ->with(['fromTable', 'fromColumn', 'toTable', 'toColumn'])
            ->get();

        $mermaid = $this->buildMermaid($diagram, $relationships);

        return Response::text(json_encode([
            'id'                   => $diagram->id,
            'name'                 => $diagram->name,
            'slug'                 => $diagram->slug,
            'team_id'              => $diagram->team_id,
            'style'                => $diagram->style,
            'database'             => $diagram->database,
            'tables_count'         => $result['tables_count'],
            'relationships_count'  => $result['relationships_count'],
            'areas_count'          => $result['areas_count'],
            'message'              => "Schema '{$diagram->name}' created successfully with {$result['tables_count']} tables, {$result['relationships_count']} relationships and {$result['areas_count']} areas.",
            'mermaid'              => $mermaid,
        ], JSON_PRETTY_PRINT));
    }

    private function buildMermaid(Diagram $diagram, \Illuminate\Database\Eloquent\Collection $relationships): string
    {
        $lines = ['erDiagram'];

        foreach ($diagram->tables as $table) {
            $lines[] = "    {$table->name} {";
            foreach ($table->columns->sortBy('order') as $col) {
                $constraint = match (true) {
                    (bool) $col->is_primary => ' PK',
                    (bool) $col->is_foreign => ' FK',
                    default                 => '',
                };
                $lines[] = "        {$col->type} {$col->name}{$constraint}";
            }
            $lines[] = '    }';
        }

        foreach ($relationships as $rel) {
            $from = $rel->fromTable?->name;
            $to   = $rel->toTable?->name;
            if (! $from || ! $to) {
                continue;
            }
            $arrow = match ($rel->type->value) {
                '1:1'   => '||--||',
                '1:n'   => '||--o{',
                'n:1'   => '}o--||',
                default => '||--o{',
            };
            $label  = $rel->label ?: 'references';
            $lines[] = "    {$from} {$arrow} {$to} : \"{$label}\"";
        }

        return implode("\n", $lines);
    }

    /**
     * Calculate canvas positions for all areas and their tables.
     * Areas are laid out horizontally (wrapping when exceeding max canvas width).
     * Tables are arranged in a mini-grid inside each area.
     *
     * @param  array<int, array{name: string, tables: string[], color?: string}>  $areasDef
     * @param  array<string, int>  $colCounts  table name => column count
     * @return array<int, array{x: int, y: int, width: int, height: int, tablePositions: array<string, array{x: int, y: int}>}>
     */
    private function calculateAreaLayouts(array $areasDef, array $colCounts): array
    {
        $layouts    = [];
        $cursorX    = 0;
        $cursorY    = 0;
        $rowMaxH    = 0;
        $maxCanvasW = 2800;

        foreach ($areasDef as $aIdx => $areaDef) {
            $tables = $areaDef['tables'];
            $n      = count($tables);
            $cols   = min($n, self::MAX_PER_ROW);
            $rows   = (int) ceil($n / $cols);

            // Estimate table height based on column count
            $tableH = max(
                array_map(
                    fn ($t) => self::TABLE_H_BASE + ($colCounts[$t] ?? 4) * self::TABLE_H_PER_COL,
                    $tables
                )
            );

            $areaW = self::AREA_PAD_X * 2 + $cols * self::TABLE_W + ($cols - 1) * self::TABLE_GAP_X;
            $areaH = self::AREA_PAD_TOP + self::AREA_PAD_BOTTOM + $rows * $tableH + ($rows - 1) * self::TABLE_GAP_Y;

            // Wrap to next row if area doesn't fit
            if ($cursorX > 0 && $cursorX + $areaW > $maxCanvasW) {
                $cursorX = 0;
                $cursorY += $rowMaxH + self::AREA_GAP;
                $rowMaxH  = 0;
            }

            // Position each table inside the area
            $tablePositions = [];
            foreach ($tables as $tIdx => $tName) {
                $col = $tIdx % $cols;
                $row = (int) floor($tIdx / $cols);

                $tablePositions[$tName] = [
                    'x' => $cursorX + self::AREA_PAD_X + $col * (self::TABLE_W + self::TABLE_GAP_X),
                    'y' => $cursorY + self::AREA_PAD_TOP + $row * ($tableH + self::TABLE_GAP_Y),
                ];
            }

            $layouts[$aIdx] = [
                'x'              => $cursorX,
                'y'              => $cursorY,
                'width'          => $areaW,
                'height'         => $areaH,
                'tablePositions' => $tablePositions,
            ];

            $cursorX += $areaW + self::AREA_GAP;
            if ($areaH > $rowMaxH) {
                $rowMaxH = $areaH;
            }
        }

        return $layouts;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name'          => $schema->string('Diagram name'),
            'team_id'       => $schema->integer('Team ID that owns the diagram'),
            'style'         => $schema->string('Color style: "neutral" or "colorful" (default: "colorful")'),
            'database'      => $schema->string('Database type: "mysql", "postgresql", or "sqlite" (default: "mysql")'),
            'description'   => $schema->string('Optional diagram description'),
            'tables'        => $schema->array('Array of tables to create')->items(
                $schema->object([
                    'name'    => $schema->string('Table name (e.g., "users", "posts")'),
                    'x'       => $schema->integer('X position (optional, auto-layout if omitted)'),
                    'y'       => $schema->integer('Y position (optional, auto-layout if omitted)'),
                    'columns' => $schema->array('Array of columns')->items(
                        $schema->object([
                            'name'          => $schema->string('Column name'),
                            'type'          => $schema->string('Column type: bigint, int, varchar, text, boolean, datetime, timestamp, json, uuid, etc.'),
                            'is_primary'    => $schema->boolean('Is primary key'),
                            'is_notnull'    => $schema->boolean('Is NOT NULL'),
                            'length'        => $schema->integer('Column length'),
                            'default_value' => $schema->string('Default value'),
                            'comment'       => $schema->string('Column comment'),
                        ])
                    ),
                ])
            ),
            'relationships' => $schema->array('Array of relationships to create (optional). FK columns are auto-created.')->items(
                $schema->object([
                    'from_table' => $schema->string('Source/parent table name'),
                    'to_table'   => $schema->string('Target/child table name'),
                    'type'       => $schema->string('Relationship type: "1:1", "1:n", "n:1", or "n:n"'),
                ])
            ),
            'areas'         => $schema->array('Optional visual grouping areas. Each area visually wraps a set of tables on the canvas.')->items(
                $schema->object([
                    'name'   => $schema->string('Area label (e.g. "Auth", "Billing", "Orders")'),
                    'color'  => $schema->string('Hex color for the area border (optional, default: #808080)'),
                    'tables' => $schema->array('Table names that belong to this area (must match names defined in "tables")')->items(
                        $schema->string('Table name')
                    ),
                ])
            ),
        ];
    }
}
