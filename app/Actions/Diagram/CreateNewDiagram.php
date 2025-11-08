<?php

namespace App\Actions\Diagram;

use App\Actions\Parse\ParseMySQLSchema;
use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Http\UploadedFile;

/**
 * @phpstan-type DTODiagram array{
 *     name: string,
 *     style: 'basic'|'colorful',
 *     database: string,
 *     description?: string|null,
 *     file?: array<UploadedFile>,
 *     type?: 'laravel'|'sql',
 * }
 */
class CreateNewDiagram
{

    public function __construct(
        public ParseMySQLSchema $parseMySQLSchemaAction
    ) {}

    /**
     * @param DTODiagram $data
     */
    public function exec(array $data): Diagram
    {
        $this->_validate($data);

        $slug = str()->slug($data['name']) . str()->random(10);

        if (data_has($data, 'file')) {
            $this->_parse(data_get($data, 'file'));
        }

        return Diagram::create([
            'name'        => $data['name'],
            'description' => data_get($data, 'description'),
            'team_id'     => Team::currentTeam()->getKey(),
            'slug'        => $slug,
        ]);
    }

    /**
     * @param DTODiagram $data
     */
    private function _validate($data): void
    {
        $validator = validator()->make($data, [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'style'       => ['required', 'in:basic,colorful'],
            'database'    => ['required', 'in:mysql,postgresql,sqlite'],
        ]);

        $validator->validate();
    }

    /**
     * @param array<UploadedFile> $file
     */
    private function _parse(array $file): void
    {
        $this->parseMySQLSchemaAction->exec($file);
    }
}
