<?php

namespace App\Actions\Diagram;

use App\Models\Relationship;
use App\Models\Table;
use App\Models\Team\Team;
use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * @phpstan-type DTORelationship array{
 *   fromTableId: int,
 *   toTableId: int,
 *   type: '1:1'|'1:n'|'n:1'|'n:n',
 *   fromColumnId: ?int,
 *   toColumnId: ?int,
 * }
 */
class AddRelationship
{
    /**
     * @param DTORelationship $data
     * @return Collection<Relationship>|Relationship
     */
    public function exec(array $data): Collection | Relationship
    {
        $this->_validate($data);

        $result = [];

        if (data_has($data, 'fromColumnId') && data_has($data, 'toColumnId')) {
            $result = (new AddRelationshipWithNewForeignKey())->exec($data);
        } else {
            $result = (new AddRelationshipWithNewForeignKey())->exec($data);
        }

        return $result;

    }

    /** @param DTORelationship $data */
    private function _validate($data): void
    {
        validator($data, [
            'fromTableId'  => ['required', 'integer'],
            'toTableId'    => ['required', 'integer'],
            'type'         => ['required', 'string', 'in:1:n,1:1,n:n,n:1'],
            'fromColumnId' => ['nullable', 'integer'],
            'toColumnId'   => ['nullable', 'integer'],
        ])->validate();

        $fromTable = Table::query()->with('diagram')->find(data_get($data, 'fromTableId'));
        $toTable = Table::query()->with('diagram')->find(data_get($data, 'toTableId'));

        if (!$fromTable || !$toTable) {
            throw new Exception("One or both tables not found.");
        }

        $this->_verifyTablesOwnership($fromTable, $toTable);
    }

     /**
     * @param Table $fromTable
     * @param Table $toTable
     * @return void
     * @throws Exception
     */
    private function _verifyTablesOwnership(Table $fromTable, Table $toTable): void
    {
        $currentTeam = Team::currentTeam();

        if (!$currentTeam) {
            throw new Exception("No team found for the current user.");
        }

        $fromTableDiagram = $fromTable->diagram;
        $toTableDiagram = $toTable->diagram;

        if (!$fromTableDiagram || !$toTableDiagram) {
            throw new Exception("One or both diagrams not found for the tables.");
        }

        if ($fromTableDiagram->team_id !== $currentTeam->getKey() ||
            $toTableDiagram->team_id !== $currentTeam->getKey()) {
            throw new Exception("One or both tables do not belong to the user's team.");
        }
    }
}
