<?php

namespace App\Actions\Parse;

use App\Actions\Diagram\AddRelationshipWithExistingForeignKey;
use App\Exceptions\TableLimitExceededException;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Table;
use App\Support\DiagramLimits;
use App\Support\Helpers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpMyAdmin\SqlParser\Components\CreateDefinition;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Statements\CreateStatement;

class ParseMySQLSchema extends BaseSQLParse
{
    public function __construct(
        private AddRelationshipWithExistingForeignKey $addRelationshipAction
    ) {}

    /**
     * @param array<UploadedFile> $files
     */
    public function exec(array $files, Diagram $diagram): void
    {
        $this->diagram = $diagram;

        // Count all tables before processing to validate limit
        $owner = $this->diagram->team?->owner;
        abort_if($owner === null, 403, 'Team owner not found');
        $limits = DiagramLimits::forUser($owner);

        $existingCount = $this->diagram->tables()->count();
        $tablesFromFiles = $this->countTablesFromFiles($files);
        $totalTables = $existingCount + $tablesFromFiles;
        if ($totalTables > $limits['table_limit']) {
            throw new TableLimitExceededException($totalTables);
        }

        foreach ($files as $file) {
            $content = $this->getFileContent($file);
            $this->parseSQL($content);
        }
    }

    /**
     * Parse SQL content and create tables/columns using PhpMyAdmin SQL Parser
     */
    private function parseSQL(string $sql): void
    {
        $parser = new Parser($sql);

        DB::transaction(function () use ($parser): void {
            /** @var array<string, Table> */
            $tables = [];

            /** @var array<array{table: string, column: string, ref_table: string, ref_column: string}> */
            $foreignKeys = [];

            // Get available colors from diagram style
            $colors = $this->diagram->colors;
            if (empty($colors)) {
                $colors = [Table::COLOR_DEFAULT];
            }

            // Convert to indexed array to work with array_rand
            $colors = array_values($colors);

            // Start from existing table count so new tables are positioned after existing ones
            $tableIndex = $this->diagram->tables()->count();

            foreach ($parser->statements as $statement) {
                if (!$statement instanceof CreateStatement) {
                    continue;
                }

                if (!$statement->name || !$statement->name->table || !$statement->fields) {
                    continue;
                }

                $tableName = $this->cleanIdentifier($statement->name->table);

                // Select a random color from available colors
                $randomColor = $colors[array_rand($colors)];

                // Calculate grid position for the table
                $tableIndex++;
                $position = Helpers::calculateGridPosition($tableIndex);

                // Create table
                $table = Table::create([
                    'diagram_id' => $this->diagram->id,
                    'name' => $tableName,
                    'x' => $position['x'],
                    'y' => $position['y'],
                    'color' => $randomColor,
                ]);

                $tables[$tableName] = $table;

                // Extract primary keys
                $primaryKeys = $this->extractPrimaryKeys($statement);

                // Parse columns
                $order = 0;
                if (is_iterable($statement->fields)) {
                    foreach ($statement->fields as $field) {
                    if (!$field->name) {
                        continue;
                    }

                    $columnName = $this->cleanIdentifier($field->name);
                    $columnType = $field->type && $field->type->name ? strtolower($field->type->name) : 'varchar';
                    $length = $field->type && $field->type->parameters ? (int) $field->type->parameters[0] : null;

                    // Check if nullable
                    $isNullable = true;
                    if ($field->options && $field->options->has('NOT NULL')) {
                        $isNullable = false;
                    }

                    // Check if auto increment
                    $isAutoIncrement = false;
                    if ($field->options && $field->options->has('AUTO_INCREMENT')) {
                        $isAutoIncrement = true;
                    }

                    // Check if primary key
                    $isPrimary = $isAutoIncrement || in_array($columnName, $primaryKeys, true);

                    // Extract default value
                    $defaultValue = null;
                    if ($field->options && property_exists($field->options, 'options')) {
                        /** @var array<int, array{name: string, equals: bool, expr: \PhpMyAdmin\SqlParser\Components\Expression|string, value: string|null}|string> $options */
                        $options = $field->options->options;
                        foreach ($options as $option) {
                            if (is_array($option) && $option['name'] === 'DEFAULT' && isset($option['value'])) {
                                $defaultValue = (string) $option['value'];
                                break;
                            }
                        }
                    }

                    // Extract comment
                    $comment = null;
                    if ($field->options && property_exists($field->options, 'options')) {
                        /** @var array<int, array{name: string, equals: bool, expr: \PhpMyAdmin\SqlParser\Components\Expression|string, value: string|null}|string> $options */
                        $options = $field->options->options;
                        foreach ($options as $option) {
                            if (is_array($option) && $option['name'] === 'COMMENT' && isset($option['value'])) {
                                $comment = trim((string) $option['value'], "'\"");
                                break;
                            }
                        }
                    }

                    Column::create([
                        'table_id' => $table->id,
                        'name' => $columnName,
                        'type' => $columnType,
                        'is_primary' => $isPrimary,
                        'is_foreign' => false,
                        'is_notnull' => !$isNullable,
                        'order' => $order++,
                        'length' => $length,
                        'default_value' => $defaultValue,
                        'comment' => $comment,
                    ]);
                    }
                }

                // Extract foreign keys
                $this->extractForeignKeys($statement, $tableName, $foreignKeys);
            }

            // Create relationships from foreign keys
            $this->createRelationships($foreignKeys, $tables);
        });
    }

    /**
     * Extract primary keys from CREATE TABLE statement
     *
     * @param CreateStatement $statement
     * @return array<string>
     */
    private function extractPrimaryKeys(CreateStatement $statement): array
    {
        /** @var array<string> */
        $primaryKeys = [];

        if (!$statement->fields || !is_iterable($statement->fields)) {
            return $primaryKeys;
        }

        foreach ($statement->fields as $field) {
            if ($field->key && $field->key->type === 'PRIMARY KEY' && $field->key->columns) {
                foreach ($field->key->columns as $column) {
                    if (isset($column['name'])) {
                        $primaryKeys[] = $this->cleanIdentifier($column['name']);
                    }
                }
            }
        }

        return $primaryKeys;
    }

    /**
     * Extract foreign keys from CREATE TABLE statement
     *
     * @param CreateStatement $statement
     * @param string $tableName
     * @param array<array{table: string, column: string, ref_table: string, ref_column: string}> &$foreignKeys
     */
    private function extractForeignKeys(CreateStatement $statement, string $tableName, array &$foreignKeys): void
    {
        if (!$statement->fields || !is_iterable($statement->fields)) {
            return;
        }

        foreach ($statement->fields as $field) {
            // Foreign keys have a key property with type 'FOREIGN KEY' and references property
            if ($field->key && $field->key->type === 'FOREIGN KEY' && $field->references) {
                $this->processForeignKey($field, $tableName, $foreignKeys);
            }
        }
    }

    /**
     * Process a foreign key definition
     *
     * @param CreateDefinition $field
     * @param string $tableName
     * @param array<array{table: string, column: string, ref_table: string, ref_column: string}> &$foreignKeys
     */
    private function processForeignKey(CreateDefinition $field, string $tableName, array &$foreignKeys): void
    {
        if (!$field->key || !$field->key->columns || !isset($field->key->columns[0]['name'])) {
            return;
        }

        if (!$field->references || !$field->references->table || !$field->references->table->table || !isset($field->references->columns[0])) {
            return;
        }

        $columnName = $this->cleanIdentifier($field->key->columns[0]['name']);
        $refTableName = $this->cleanIdentifier($field->references->table->table);
        $refColumnName = $this->cleanIdentifier($field->references->columns[0]);

        $foreignKeys[] = [
            'table' => $tableName,
            'column' => $columnName,
            'ref_table' => $refTableName,
            'ref_column' => $refColumnName,
        ];
    }

    /**
     * Clean identifier by removing backticks and quotes
     */
    private function cleanIdentifier(string $identifier): string
    {
        return trim($identifier, '`"\' ');
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

    /**
     * Count total number of unique tables from all files
     *
     * @param array<UploadedFile> $files
     * @return int
     */
    private function countTablesFromFiles(array $files): int
    {
        /** @var array<string, bool> */
        $uniqueTables = [];

        foreach ($files as $file) {
            $content = $this->getFileContent($file);
            $parser = new Parser($content);

            foreach ($parser->statements as $statement) {
                if (!$statement instanceof CreateStatement) {
                    continue;
                }

                if (!$statement->name || !$statement->name->table) {
                    continue;
                }

                $tableName = $this->cleanIdentifier($statement->name->table);
                $uniqueTables[$tableName] = true;
            }
        }

        return count($uniqueTables);
    }
}
