<?php

namespace App\Actions\Diagram;

use App\Models\Relationship;
use App\Models\Table;
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
     * @return Collection<int,Relationship>|Relationship
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

        $fromTable = Table::query()->find(data_get($data, 'fromTableId'));
        $toTable = Table::query()->find(data_get($data, 'toTableId'));

        if (!$fromTable || !$toTable) {
            throw new Exception("One or both tables not found.");
        }

         /** @var Table $fromTable */
         /** @var Table $toTable */

        $this->_verifyTablesOwnership($fromTable, $toTable);
    }

     /**
     * @param Table $fromTable
     * @param Table $toTable
     * @throws Exception
     */
    private function _verifyTablesOwnership(Table $fromTable, Table $toTable): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            throw new Exception("No user found.");
        }

        $fromTableDiagram = $fromTable->diagram;
        $toTableDiagram = $toTable->diagram;

        if (is_null($fromTableDiagram) || is_null($toTableDiagram)) {
            throw new Exception("One or both diagrams not found for the tables.");
        }

        $ids = array_unique([$fromTableDiagram->team_id, $toTableDiagram->team_id]);

        if ($user->ownedTeams()->whereIn('team.id', $ids)->count() !== count($ids)) {
            throw new Exception("One or both tables do not belong to the user's workspace.");
        }
    }
}
