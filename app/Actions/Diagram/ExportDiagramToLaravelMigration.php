<?php

namespace App\Actions\Diagram;

use App\Models\Diagram;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExportDiagramToLaravelMigration
{
    private array $typeMapping;
    private array $defaultLengths;
    private array $specialMethods;

    public function __construct()
    {
        $config = config('migration-generator');
        $this->typeMapping = $config['type_mapping'];
        $this->defaultLengths = $config['default_lengths'];
        $this->specialMethods = $config['special_methods'];
    }

    /**
     * @return array<int, array{filename: string, content: string}>
     */
    public function exec(Diagram $diagram): array
    {
        if (! Auth::user()?->isPro()) {
            throw ValidationException::withMessages([
                'export' => __('Export is available on the Pro plan or higher.'),
            ]);
        }

        $diagram->loadMissing('tables.columns');

        $relationships = $diagram->relationships()
            ->with(['fromTable', 'fromColumn', 'toTable', 'toColumn'])
            ->get();

        $tables = $diagram->tables;
        $sorted = $this->topologicalSort($tables, $relationships);

        $files = [];
        $timestamp = now()->format('Y_m_d');

        foreach ($sorted as $index => $table) {
            $seq = str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT);
            $tableName = $table->name;
            $filename = "{$timestamp}_{$seq}_create_{$tableName}_table.php";

            $tableRelationships = $relationships->filter(
                fn ($rel) => $rel->to_table_id === $table->id
            );

            $content = $this->generateMigration($table, $tableRelationships);

            $files[] = [
                'filename' => $filename,
                'content'  => $content,
            ];
        }

        return $files;
    }

    private function generateMigration(mixed $table, Collection $relationships): string
    {
        $columns = $table->columns->sortBy('order')->values();
        $lines = $this->buildColumnLines($columns, $relationships);
        $tableName = $table->name;
        $className = 'Create' . Str::studly($tableName) . 'Table';
        $linesStr = implode("\n", array_map(fn ($l) => "            {$l}", $lines));

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
{$linesStr}
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};

PHP;
    }

    /**
     * @return string[]
     */
    private function buildColumnLines(Collection $columns, Collection $relationships): array
    {
        $lines = [];
        $consumed = [];

        // Detect special methods
        foreach ($this->specialMethods as $key => $spec) {
            $specColumns = $spec['columns'];
            $criteria = $spec['criteria'];

            $allMatch = true;
            foreach ($specColumns as $colName) {
                $col = $columns->firstWhere('name', $colName);
                if (!$col) {
                    $allMatch = false;
                    break;
                }
                foreach ($criteria as $attr => $val) {
                    $colVal = $col->{$attr};
                    if (is_bool($val)) {
                        $colVal = (bool) $colVal;
                    }
                    if ($colVal !== $val) {
                        $allMatch = false;
                        break 2;
                    }
                }
            }

            if ($allMatch) {
                foreach ($specColumns as $colName) {
                    $consumed[] = $colName;
                }
            }
        }

        // Build lines in order
        foreach ($columns as $col) {
            if (in_array($col->name, $consumed)) {
                // Emit special method at position of first matching column
                $specialLine = $this->getSpecialMethodLine($col->name);
                if ($specialLine && !in_array($specialLine, $lines)) {
                    $lines[] = $specialLine;
                }
                continue;
            }

            $lines[] = $this->buildColumnLine($col, $relationships);
        }

        return $lines;
    }

    private function getSpecialMethodLine(string $columnName): ?string
    {
        foreach ($this->specialMethods as $spec) {
            if (in_array($columnName, $spec['columns'])) {
                return $spec['method'];
            }
        }
        return null;
    }

    private function buildColumnLine(mixed $col, Collection $relationships): string
    {
        // Check if this is a foreign key with a relationship
        if ($col->is_foreign) {
            $rel = $relationships->first(fn ($r) => $r->to_column_id === $col->id);
            if ($rel) {
                return $this->buildForeignKeyLine($col, $rel);
            }

            // FK without relationship — use unsignedBigInteger
            $line = "\$table->unsignedBigInteger('{$col->name}')";
            $line .= $this->appendModifiers($col);
            return $line . ';';
        }

        // Normal column
        $type = strtolower($col->type);
        $method = $this->typeMapping[$type] ?? 'string';

        $line = "\$table->{$method}('{$col->name}'";

        // Add length if non-default
        if ($col->length && isset($this->defaultLengths[$type])) {
            if ((int) $col->length !== $this->defaultLengths[$type]) {
                $line .= ", {$col->length}";
            }
        } elseif ($col->length && in_array($type, ['varchar', 'char'])) {
            $line .= ", {$col->length}";
        }

        $line .= ')';
        $line .= $this->appendModifiers($col);

        return $line . ';';
    }

    private function buildForeignKeyLine(mixed $col, mixed $rel): string
    {
        $line = "\$table->foreignId('{$col->name}')";

        // Nullable before constrained
        if (!$col->is_notnull) {
            $line .= '->nullable()';
        }

        // Always use explicit constrained('table') with the real referenced table name.
        // Laravel's constrained() without args infers from column name (user_id -> users),
        // but the column name may not match the referenced table (e.g. writer_id -> authors).
        $referencedTable = $rel->fromTable?->name;
        $line .= "->constrained('{$referencedTable}')";

        // Add comment if present
        if ($col->comment) {
            $escaped = addslashes($col->comment);
            $line .= "->comment('{$escaped}')";
        }

        return $line . ';';
    }

    private function appendModifiers(mixed $col): string
    {
        $modifiers = '';

        if (!$col->is_notnull) {
            $modifiers .= '->nullable()';
        }

        if ($col->default_value !== null && $col->default_value !== '') {
            $default = $col->default_value;
            if (is_numeric($default)) {
                $modifiers .= "->default({$default})";
            } elseif (in_array(strtolower($default), ['true', 'false'])) {
                $modifiers .= "->default({$default})";
            } elseif (strtoupper($default) === 'NULL') {
                $modifiers .= '->default(null)';
            } else {
                $escaped = addslashes($default);
                $modifiers .= "->default('{$escaped}')";
            }
        }

        if ($col->comment) {
            $escaped = addslashes($col->comment);
            $modifiers .= "->comment('{$escaped}')";
        }

        return $modifiers;
    }

    /**
     * @return Collection
     */
    private function topologicalSort(Collection $tables, Collection $relationships): Collection
    {
        $tableMap = $tables->keyBy('id');
        $deps = [];

        foreach ($tables as $table) {
            $deps[$table->id] = [];
        }

        foreach ($relationships as $rel) {
            $parentId = $rel->from_table_id; // parent/owner (referenced)
            $childId = $rel->to_table_id;    // child (has FK)
            // Child depends on parent — parent must come first
            if ($parentId !== $childId && isset($deps[$parentId]) && isset($deps[$childId])) {
                $deps[$childId][] = $parentId;
            }
        }

        $sorted = [];
        $visited = [];
        $visiting = [];

        $visit = function (int $id) use (&$visit, &$sorted, &$visited, &$visiting, $deps) {
            if (isset($visited[$id])) return;
            if (isset($visiting[$id])) return; // Break circular dependency

            $visiting[$id] = true;

            foreach ($deps[$id] as $depId) {
                $visit($depId);
            }

            unset($visiting[$id]);
            $visited[$id] = true;
            $sorted[] = $id;
        };

        foreach ($tables as $table) {
            $visit($table->id);
        }

        return collect($sorted)->map(fn ($id) => $tableMap[$id]);
    }
}
