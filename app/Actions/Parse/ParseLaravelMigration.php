<?php

namespace App\Actions\Parse;

use App\Actions\Diagram\AddRelationshipWithExistingForeignKey;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Table;
use App\Support\Helpers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParseLaravelMigration extends BaseSQLParse
{
    private int $tableIndex = 0;

    public function __construct(
        private AddRelationshipWithExistingForeignKey $addRelationshipAction
    ) {}

    /**
     * @param array<UploadedFile> $files
     */
    public function exec(array $files, Diagram $diagram): void
    {
        $tableObserver = Table::getEventDispatcher();

        Table::unsetEventDispatcher();

        $this->diagram = $diagram;
        $this->tableIndex = 0; // Reset for this import

        // Process ALL files in a SINGLE transaction so foreign keys can reference tables from other files
        DB::transaction(function () use ($files): void {
            /** @var array<string, Table> */
            $allTables = [];

            /** @var array<array{table: string, column: string, ref_table: string, ref_column: string}> */
            $allForeignKeys = [];

            // Get available colors from diagram style
            $colors = $this->diagram->getColors();
            if (empty($colors)) {
                $colors = [Table::COLOR_DEFAULT];
            }

            // First pass: Create all tables and columns from all files
            foreach ($files as $file) {
                $content = $this->getFileContent($file);
                $createPatterns = $this->extractSchemaCreateBlocks($content);

                foreach ($createPatterns as $schemaBlock) {
                    $tableName = $schemaBlock['table'];
                    $blueprint = $schemaBlock['blueprint'];

                    // Select a random color from available colors
                    $randomColor = $colors[array_rand($colors)];

                    // Calculate grid position for the table
                    $this->tableIndex++;
                    $position = Helpers::calculateGridPosition($this->tableIndex);

                    // Create table
                    $table = Table::create([
                        'diagram_id' => $this->diagram->id,
                        'name' => $tableName,
                        'x' => $position['x'],
                        'y' => $position['y'],
                        'color' => $randomColor,
                    ]);

                    $allTables[$tableName] = $table;

                    // Parse columns from blueprint
                    $this->parseColumns($blueprint, $table);

                    // Extract foreign keys from blueprint (but don't create relationships yet)
                    $this->extractForeignKeysFromBlueprint($blueprint, $tableName, $allForeignKeys);
                }
            }

            // Process Schema::table() blocks to add/modify columns in existing tables
            foreach ($files as $file) {
                $content = $this->getFileContent($file);
                $tablePatterns = $this->extractSchemaTableBlocks($content);

                foreach ($tablePatterns as $schemaBlock) {
                    $tableName = $schemaBlock['table'];
                    $blueprint = $schemaBlock['blueprint'];

                    // Check if table exists in our imported tables
                    if (isset($allTables[$tableName])) {
                        $table = $allTables[$tableName];

                        // Parse new columns from blueprint
                        $this->parseColumns($blueprint, $table);

                        // Extract foreign keys from blueprint
                        $this->extractForeignKeysFromBlueprint($blueprint, $tableName, $allForeignKeys);
                    }
                }
            }

            // Second pass: Create all relationships now that all tables exist
            $this->createRelationships($allForeignKeys, $allTables);
        });

        if ($tableObserver) {
            Table::setEventDispatcher($tableObserver);
        }
    }

    /**
     * Extract Schema::create() blocks from migration content
     *
     * @return array<array{table: string, blueprint: string}>
     */
    private function extractSchemaCreateBlocks(string $content): array
    {
        $blocks = [];
        $pattern = config('migration-parser.schema_patterns.create_table');

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $blocks[] = [
                'table' => $match[1],
                'blueprint' => $match[2],
            ];
        }

        return $blocks;
    }

    /**
     * Extract Schema::table() blocks from migration content
     *
     * @return array<array{table: string, blueprint: string}>
     */
    private function extractSchemaTableBlocks(string $content): array
    {
        $blocks = [];
        $pattern = config('migration-parser.schema_patterns.alter_table',
            '/Schema::table\s*\(\s*[\'"](\w+)[\'"]\s*,\s*function\s*\([^)]*\)\s*{([^}]*)}/s'
        );

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $blocks[] = [
                'table' => $match[1],
                'blueprint' => $match[2],
            ];
        }

        return $blocks;
    }

    /**
     * Parse columns from blueprint code
     */
    private function parseColumns(string $blueprint, Table $table): void
    {
        // Split by lines and process each line
        $lines = explode("\n", $blueprint);

        // Get the current max order for existing columns
        $order = Column::where('table_id', $table->id)->max('order') ?? -1;
        $order++;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, '//') || str_starts_with($line, '/*')) {
                continue;
            }

            // Handle special methods first (timestamps, softDeletes, etc)
            if ($this->handleSpecialMethod($line, $table, $order)) {
                continue;
            }

            // Check if line should be skipped (foreign keys, indexes, etc)
            if ($this->shouldSkipLine($line)) {
                continue;
            }

            // Extract column definition
            $columnData = $this->extractColumnData($line);

            if ($columnData) {
                // Check if column already exists (for Schema::table modifications)
                $existingColumn = Column::where('table_id', $table->id)
                    ->where('name', $columnData['name'])
                    ->first();

                if (!$existingColumn) {
                    // Only create if column doesn't exist
                    Column::create([
                        'table_id' => $table->id,
                        'name' => $columnData['name'],
                        'type' => $columnData['type'],
                        'is_primary' => $columnData['is_primary'],
                        'is_foreign' => $columnData['is_foreign'],
                        'is_notnull' => $columnData['is_notnull'],
                        'order' => $order++,
                        'length' => $columnData['length'],
                        'default_value' => $columnData['default_value'],
                        'comment' => $columnData['comment'],
                    ]);
                }
            }
        }
    }

    /**
     * Extract column data from a blueprint line
     *
     * @return array{name: string, type: string, is_primary: bool, is_foreign: bool, is_notnull: bool, length: int|null, default_value: string|null, comment: string|null}|null
     */
    private function extractColumnData(string $line): ?array
    {
        // Special case for id() method without parameters
        if (preg_match('/\$\w+\s*->\s*id\s*\(\s*\)/i', $line)) {
            return [
                'name' => 'id',
                'type' => 'bigint',
                'is_primary' => true,
                'is_foreign' => false,
                'is_notnull' => true,
                'length' => null,
                'default_value' => null,
                'comment' => null,
            ];
        }

        // Pattern to match $table->columnType('column_name') or $table->columnType('column_name', length)
        $pattern = config('migration-parser.column_pattern');
        if (!preg_match($pattern, $line, $matches)) {
            return null;
        }

        $laravelType = $matches[1];
        $columnName = $matches[2];
        $length = isset($matches[3]) ? (int) $matches[3] : null;

        // Map Laravel column types to database types
        $type = $this->mapLaravelTypeToDatabase($laravelType);

        // Check for primary key
        $isPrimary = $laravelType === 'id' || str_contains($line, '->primary()');

        // Check for foreign key - more comprehensive detection
        // ForeignId types or columns with foreign() method or constrained() method
        $foreignKeyTypes = config('migration-parser.foreign_key_types', []);
        $isForeign = in_array($laravelType, $foreignKeyTypes, true) ||
                     str_contains($line, '->foreign(') ||
                     str_contains($line, '->constrained(');

        // Check for nullable
        $isNullable = str_contains($line, '->nullable()');

        // Extract default value
        $defaultValue = null;
        if (preg_match('/->default\s*\(\s*[\'"]?([^\'"(),]+)[\'"]?\s*\)/i', $line, $defaultMatch)) {
            $defaultValue = $defaultMatch[1];
        }

        // Extract comment
        $comment = null;
        if (preg_match('/->comment\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/i', $line, $commentMatch)) {
            $comment = $commentMatch[1];
        }

        // Set default length for certain types
        if ($length === null) {
            $length = $this->getDefaultLength($type);
        }

        return [
            'name' => $columnName,
            'type' => $type,
            'is_primary' => $isPrimary,
            'is_foreign' => $isForeign,
            'is_notnull' => !$isNullable,
            'length' => $length,
            'default_value' => $defaultValue,
            'comment' => $comment,
        ];
    }

    /**
     * Map Laravel column type to database type
     */
    private function mapLaravelTypeToDatabase(string $laravelType): string
    {
        $typeMap = config('migration-parser.type_mapping', []);
        return $typeMap[$laravelType] ?? 'varchar';
    }

    /**
     * Get default length for a type
     */
    private function getDefaultLength(string $type): ?int
    {
        $defaults = config('migration-parser.default_lengths', []);
        return $defaults[$type] ?? null;
    }

    /**
     * Handle special blueprint methods (timestamps, softDeletes, etc)
     * Returns true if the line was handled, false otherwise
     */
    private function handleSpecialMethod(string $line, Table $table, int &$order): bool
    {
        $specialMethods = config('migration-parser.special_methods', []);

        foreach ($specialMethods as $method => $config) {
            if (str_contains($line, $method . '()')) {
                foreach ($config['columns'] as $columnConfig) {
                    // Check if column already exists
                    $existingColumn = Column::where('table_id', $table->id)
                        ->where('name', $columnConfig['name'])
                        ->first();

                    if (!$existingColumn) {
                        Column::create([
                            'table_id' => $table->id,
                            'name' => $columnConfig['name'],
                            'type' => $columnConfig['type'],
                            'is_primary' => $columnConfig['is_primary'] ?? false,
                            'is_foreign' => false,
                            'is_notnull' => !($columnConfig['nullable'] ?? false),
                            'order' => $order++,
                            'length' => $columnConfig['length'] ?? null,
                            'default_value' => null,
                            'comment' => null,
                        ]);
                    }
                }
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a line should be skipped during column parsing
     */
    private function shouldSkipLine(string $line): bool
    {
        $skipPatterns = config('migration-parser.skip_patterns', []);

        foreach ($skipPatterns as $pattern) {
            if (str_contains($line, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract foreign keys from blueprint
     *
     * @param string $blueprint
     * @param string $tableName
     * @param array<array{table: string, column: string, ref_table: string, ref_column: string}> &$foreignKeys
     */
    private function extractForeignKeysFromBlueprint(string $blueprint, string $tableName, array &$foreignKeys): void
    {
        $patterns = config('migration-parser.foreign_key_patterns', []);

        foreach ($patterns as $patternName => $patternConfig) {
            $this->extractForeignKeysByPattern($blueprint, $tableName, $foreignKeys, $patternName, $patternConfig);
        }
    }

    /**
     * Extract foreign keys using a specific pattern configuration
     *
     * @param string $blueprint
     * @param string $tableName
     * @param array<array{table: string, column: string, ref_table: string, ref_column: string}> &$foreignKeys
     * @param string $patternName
     * @param array<string, mixed> $patternConfig
     */
    private function extractForeignKeysByPattern(
        string $blueprint,
        string $tableName,
        array &$foreignKeys,
        string $patternName,
        array $patternConfig
    ): void {
        preg_match_all($patternConfig['regex'], $blueprint, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $result = match ($patternName) {
                'explicit' => [
                    'column' => $match[1] ?? null,
                    'ref_table' => $match[3] ?? null,
                    'ref_column' => $match[2] ?? null,
                ],
                'foreign_id_explicit' => [
                    'column' => $match[1] ?? null,
                    'ref_table' => $match[2] ?? null,
                    'ref_column' => 'id',
                ],
                'foreign_id_inferred' => [
                    'column' => $match[1] ?? null,
                    'ref_table' => isset($match[1]) ? $this->inferTableNameFromColumn($match[1]) : null,
                    'ref_column' => 'id',
                ],
                'foreign_id_for' => $this->processForeignIdFor($match),
                'foreign_uuid_ulid' => [
                    'column' => $match[1] ?? null,
                    'ref_table' => isset($match[2]) && !empty($match[2])
                        ? $match[2]
                        : (isset($match[1]) ? $this->inferTableNameFromColumn($match[1]) : null),
                    'ref_column' => 'id',
                ],
                default => null,
            };

            if ($result && $result['column'] && $result['ref_table'] && $result['ref_column']) {
                $this->addForeignKeyIfNotExists(
                    $foreignKeys,
                    $tableName,
                    $result['column'],
                    $result['ref_table'],
                    $result['ref_column']
                );
            }
        }
    }

    /**
     * Process foreignIdFor pattern matches
     *
     * @param array<int, string> $match
     * @return array{column: string, ref_table: string, ref_column: string}
     */
    private function processForeignIdFor(array $match): array
    {
        // Extract model name (e.g., User from App\Models\User)
        $modelClass = $match[1];
        $modelName = basename(str_replace('\\', '/', $modelClass));

        // Custom column name or infer from model
        $columnName = $match[2] ?? Str::snake($modelName) . '_id';

        // Infer table name from model (User -> users)
        $refTable = Str::plural(Str::snake($modelName));

        return [
            'column' => $columnName,
            'ref_table' => $refTable,
            'ref_column' => 'id',
        ];
    }

    /**
     * Infer table name from foreign key column name (e.g., user_id -> users)
     */
    private function inferTableNameFromColumn(string $columnName): string
    {
        // Remove _id suffix
        $tableName = preg_replace('/_id$/', '', $columnName);

        // Pluralize the table name (simple pluralization)
        return Str::plural($tableName ?? $columnName);
    }

    /**
     * Add foreign key to array if it doesn't exist yet
     *
     * @param array<array{table: string, column: string, ref_table: string, ref_column: string}> &$foreignKeys
     * @param string $tableName
     * @param string $columnName
     * @param string $refTable
     * @param string $refColumn
     */
    private function addForeignKeyIfNotExists(array &$foreignKeys, string $tableName, string $columnName, string $refTable, string $refColumn): void
    {
        foreach ($foreignKeys as $fk) {
            if ($fk['table'] === $tableName && $fk['column'] === $columnName) {
                return; // Already exists
            }
        }

        $foreignKeys[] = [
            'table' => $tableName,
            'column' => $columnName,
            'ref_table' => $refTable,
            'ref_column' => $refColumn,
        ];
    }

    /**
     * Create relationships from foreign keys
     *
     * @param array<array{table: string, column: string, ref_table: string, ref_column: string}> $foreignKeys
     * @param array<string, Table> $tables
     */
    private function createRelationships(array $foreignKeys, array $tables): void
    {
        foreach ($foreignKeys as $fk) {
            $this->createSingleRelationship($fk, $tables);
        }
    }

    /**
     * Create a single relationship with existing foreign key using the dedicated action
     *
     * @param array{table: string, column: string, ref_table: string, ref_column: string} $foreignKeyData
     * @param array<string, Table> $tables
     */
    private function createSingleRelationship(array $foreignKeyData, array $tables): void
    {
        // Get tables: child (with FK) and owner (referenced)
        $childTable = $tables[$foreignKeyData['table']] ?? null;
        $ownerTable = $tables[$foreignKeyData['ref_table']] ?? null;

        if (!$childTable || !$ownerTable) {
            return;
        }

        // Get columns: FK in child table and PK in owner table
        $foreignKeyColumn = Column::where('table_id', $childTable->id)
            ->where('name', $foreignKeyData['column'])
            ->first();

        $primaryKeyColumn = Column::where('table_id', $ownerTable->id)
            ->where('name', $foreignKeyData['ref_column'])
            ->first();

        if (!$foreignKeyColumn || !$primaryKeyColumn) {
            return;
        }

        // Use the dedicated Action to create the relationship
        $this->addRelationshipAction->exec([
            'ownerTableId' => $ownerTable->id,
            'ownerColumnId' => $primaryKeyColumn->id,
            'childTableId' => $childTable->id,
            'foreignKeyColumnId' => $foreignKeyColumn->id,
        ]);
    }
}

