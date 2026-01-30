<?php

namespace App\Actions\Diagram;

use App\Models\Column;
use App\Models\Diagram;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type DTODiagramUpdate array{
 *     name: string,
 *     style: 'neutral'|'colorful',
 *     database: string,
 *     description?: string|null,
 * }
 */
class UpdateDiagram
{
    /**
     * @param DTODiagramUpdate $data
     */
    public function exec(Diagram $diagram, array $data): Diagram
    {
        $this->_validate($data);

        // If style or database changed, delete all tables
        if ($this->_hasModifiedStyleOrDatabase($diagram, $data)) {
            DB::transaction(function () use ($diagram) {
                $tableIds = $diagram->tables()->pluck('id');
                $diagram->relationships()->delete();
                Column::whereIn('table_id', $tableIds)->delete();
                $diagram->tables()->delete();
                $diagram->areas()->delete();
                $diagram->notes()->delete();
            });
        }

        // Update basic info
        $diagram->name = $data['name'];
        $diagram->description = data_get($data, 'description');
        $diagram->style = data_get($data, 'style');
        $diagram->database = data_get($data, 'database');

        $diagram->save();

        return $diagram;
    }

    /**
     * @param DTODiagramUpdate $data
     */
    private function _validate($data): void
    {
        validator($data, [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'style'       => ['required', 'in:neutral,colorful'],
            'database'    => ['required', 'in:mysql,postgresql,sqlite'],
        ])->validate();
    }

    /**
     * @param DTODiagramUpdate $data
     */
    private function _hasModifiedStyleOrDatabase(Diagram $diagram, array $data): bool
    {
        return $diagram->style !== data_get($data, 'style')
            || $diagram->database !== data_get($data, 'database');
    }
}
