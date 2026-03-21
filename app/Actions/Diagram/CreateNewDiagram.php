<?php

namespace App\Actions\Diagram;

use App\Actions\Parse\ParseMySQLSchema;
use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Http\UploadedFile;
use App\Actions\Parse\ParseLaravelMigration;
use App\Exceptions\TableLimitExceededException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * @phpstan-type DTODiagram array{
 *     name: string,
 *     style: 'neutral'|'colorful',
 *     database: string,
 *     description?: string|null,
 *     file?: array<UploadedFile>,
 *     type?: 'migration'|'sql',
 * }
 */
class CreateNewDiagram
{

    public function __construct(
        public ParseMySQLSchema $parseMySQLSchemaAction,
        public ParseLaravelMigration $parseLaravelMigrationAction
    ) {}

    /**
     * @param DTODiagram $data
     */
    public function exec(array $data): Diagram
    {
        $this->_validate($data);

        $currentTeam = Team::currentTeam();

        if (!$currentTeam) {
            throw new \Exception('No team found for the current user.');
        }

        $slug = str()->slug($data['name']) . '-' . str()->random(10);

        $diagram = Diagram::create([
            'name'        => $data['name'],
            'description' => data_get($data, 'description'),
            'style'       => data_get($data, 'style'),
            'database'    => data_get($data, 'database'),
            'team_id'     => $currentTeam->getKey(),
            'slug'        => $slug,
        ]);

        if (data_has($data, 'file') && !empty(data_get($data, 'file')) && data_has($data, 'type')) {
            if (! Auth::user()?->isPro()) {
                $diagram->delete();
                throw ValidationException::withMessages([
                    'importForm.file' => __('SQL and migration import are available on the Pro plan or higher.'),
                ]);
            }

            $diagram->imported_at = now();
            $diagram->save();

            try {
                match (data_get($data, 'type')) {
                    'sql' => $this->_parseSqlFile(data_get($data, 'file'), $diagram),
                    'migration' => $this->_parseMigrationFile(data_get($data, 'file'), $diagram),
                    default => throw new \Exception('Invalid file type'),
                };
            } catch (TableLimitExceededException $e) {
                $diagram->delete();
                throw ValidationException::withMessages(['importForm.file' => __('Table limit exceeded with :totalTables tables', ['totalTables' => $e->getTotalTables()])]);
            }
        }

        return $diagram;
    }

    /**
     * @param DTODiagram $data
     */
    private function _validate($data): void
    {
        $validator = validator()->make($data, [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'style'       => ['required', 'in:neutral,colorful'],
            'database'    => ['required', 'in:mysql,postgresql,sqlite'],
        ]);

        $validator->validate();
    }

    /**
     * @param array<UploadedFile> $file
     */
    private function _parseSqlFile(array $file, Diagram $diagram): void
    {
        match ($diagram->database) {
            'mysql' => $this->parseMySQLSchemaAction->exec($file, $diagram),
            //todo: add other database types => postgresql, sqlite
            default => throw new \Exception('Invalid database type'),
        };
    }

    /**
     * @param array<UploadedFile> $file
     */
    private function _parseMigrationFile(array $file, Diagram $diagram): void
    {
        $this->parseLaravelMigrationAction->exec($file, $diagram);
    }
}
