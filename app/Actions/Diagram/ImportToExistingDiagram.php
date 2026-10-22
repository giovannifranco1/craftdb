<?php

namespace App\Actions\Diagram;

use App\Actions\Parse\ParseLaravelMigration;
use App\Actions\Parse\ParseMySQLSchema;
use App\Models\Column;
use App\Models\Diagram;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-param array<UploadedFile> $files
 */
class ImportToExistingDiagram
{
    public function __construct(
        public ParseMySQLSchema $parseMySQLSchemaAction,
        public ParseLaravelMigration $parseLaravelMigrationAction
    ) {}

    /**
     * @param  array<UploadedFile>  $files
     */
    public function exec(Diagram $diagram, array $files, string $type): void
    {
        $this->_validate($diagram, $files, $type);

        match ($type) {
            'sql' => $this->_importSql($files, $diagram),
            'migration' => $this->_parseMigrationFile($files, $diagram),
            default => throw ValidationException::withMessages(['importForm.type' => __('Invalid import type.')]),
        };

        $diagram->imported_at = now();
        $diagram->save();
    }

    /**
     * SQL import resets the entire diagram before importing.
     * Both reset and parse run in a single transaction so parse failures roll back the reset.
     *
     * @param  array<UploadedFile>  $files
     */
    private function _importSql(array $files, Diagram $diagram): void
    {
        DB::transaction(function () use ($files, $diagram): void {
            $this->_resetDiagram($diagram);
            $this->_parseSqlFile($files, $diagram);
        });
    }

    /**
     * Remove all tables, relationships, areas and notes from the diagram.
     */
    private function _resetDiagram(Diagram $diagram): void
    {
        $diagram->relationships()->delete();
        $tableIds = $diagram->tables()->pluck('id');
        Column::whereIn('table_id', $tableIds)->delete();
        $diagram->tables()->delete();
        $diagram->areas()->delete();
        $diagram->notes()->delete();
    }

    /**
     * @param  array<UploadedFile>  $files
     */
    private function _validate(Diagram $diagram, array $files, string $type): void
    {
        $database = $diagram->database ?? 'mysql';

        if ($type === 'sql' && $database !== 'mysql') {
            throw ValidationException::withMessages(['importForm.type' => __('SQL import is only available for MySQL diagrams.')]);
        }

        validator()->make(
            [
                'files' => $files,
                'type' => $type,
            ],
            [
                'files' => ['required', 'array'],
                'files.*' => ['file'],
                'type' => ['required', 'in:sql,migration'],
            ]
        )->validate();
    }

    /**
     * @param  array<UploadedFile>  $files
     */
    private function _parseSqlFile(array $files, Diagram $diagram): void
    {
        match ($diagram->database) {
            'mysql' => $this->parseMySQLSchemaAction->exec($files, $diagram),
            default => throw ValidationException::withMessages(['importForm.type' => __('SQL import is only available for MySQL diagrams.')]),
        };
    }

    /**
     * @param  array<UploadedFile>  $files
     */
    private function _parseMigrationFile(array $files, Diagram $diagram): void
    {
        $this->parseLaravelMigrationAction->exec($files, $diagram);
    }
}
