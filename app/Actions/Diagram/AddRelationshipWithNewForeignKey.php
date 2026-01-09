<?php

namespace App\Actions\Diagram;

use App\Enums\RelationshipType;
use App\Models\Column;
use App\Models\Relationship;
use App\Models\Table;
use App\Support\Helpers;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type DTORelationship array{
 *   fromTableId: int,
 *   toTableId: int,
 *   type: '1:1'|'1:n'|'n:1'|'n:n'
 * }
 */
class AddRelationshipWithNewForeignKey
{
    /**
     * @param DTORelationship $data
     * @return Collection<Relationship>|Relationship
     */
    public function exec(array $data): Collection | Relationship
    {
        $this->_validate($data);

        $fromTable = Table::query()->with(['columns', 'diagram'])->find(data_get($data, 'fromTableId'));
        $toTable = Table::query()->with(['columns', 'diagram'])->find(data_get($data, 'toTableId'));

        $type = RelationshipType::from($data['type']);

        return match ($type) {
            RelationshipType::ONE_TO_ONE, RelationshipType::ONE_TO_MANY => $this->_addOneToOneOrMany($fromTable, $toTable, $type),
            RelationshipType::MANY_TO_ONE => $this->_addBelongsTo($fromTable, $toTable),
            RelationshipType::MANY_TO_MANY => $this->_addManyToMany($fromTable, $toTable),
        };
    }

    /** @param DTORelationship $data */
    private function _validate($data): void
    {
        validator($data, [
            'fromTableId' => ['required', 'integer'],
            'toTableId'   => ['required', 'integer'],
            'type'        => ['required', 'string', 'in:1:n,1:1,n:n,n:1'],
        ])->validate();
    }

    /**
     * Add Relationship 1:1 or 1:n
     *
     * @param Table $fromTable
     * @param Table $toTable
     * @param RelationshipType $type
     * @return Relationship
     */
    private function _addOneToOneOrMany(Table $fromTable, Table $toTable, RelationshipType $type): Relationship
    {
        return DB::transaction(function () use ($fromTable, $toTable, $type) {
            $fromColumnPrimary = $fromTable->columns()->where('is_primary', true)->first() ?? $this->_createDefaultPK($fromTable);
            $toColumnForeign = $this->_createDefaultFK($toTable, str()->snake($fromTable->name) . '_id');

            return Relationship::create([
                'from_table_id'  => $fromTable->id,
                'to_table_id'    => $toTable->id,
                'from_column_id' => $fromColumnPrimary->id,
                'to_column_id'   => $toColumnForeign->id,
                'type'           => $type,
            ]);
        });
    }

    /**
     * Add Relationship n:1 (belongsTo)
     *
     * @param Table $belongsToTable The table that belongs to (child)
     * @param Table $ownerTable The table that owns (parent)
     * @return Relationship
     */
    private function _addBelongsTo(Table $belongsToTable, Table $ownerTable): Relationship
    {
        return DB::transaction(function () use ($belongsToTable, $ownerTable) {
            $ownerColumnPrimary = $ownerTable->columns()->where('is_primary', true)->first() ?? $this->_createDefaultPK($ownerTable);
            $belongsToColumnForeign = $this->_createDefaultFK($belongsToTable, str()->snake($ownerTable->name) . '_id');

            return Relationship::create([
                'from_table_id'  => $ownerTable->id,
                'to_table_id'    => $belongsToTable->id,
                'from_column_id' => $ownerColumnPrimary->id,
                'to_column_id'   => $belongsToColumnForeign->id,
                'type'           => RelationshipType::MANY_TO_ONE,
            ]);
        });
    }

    /**
     * Add Many-to-Many relationship
     * Creates a pivot table and two relationships (pivot -> fromTable, pivot -> toTable)
     *
     * @param Table $fromTable
     * @param Table $toTable
     * @return Collection<Relationship>
     */
    private function _addManyToMany(Table $fromTable, Table $toTable): Collection
    {
        return DB::transaction(function () use ($fromTable, $toTable) {
            $diagram = $fromTable->diagram;

            if (!$diagram) {
                throw new Exception("Diagram not found for the tables.");
            }

            // Calculate pivot table position (between the two tables)
            $pivotX = (int) (($fromTable->x + $toTable->x) / 2);
            $pivotY = (int) (($fromTable->y + $toTable->y) / 2);

            $fromColumnPrimary = $fromTable->columns()->where('is_primary', true)->first() ?? $this->_createDefaultPK($fromTable);
            $toColumnPrimary = $toTable->columns()->where('is_primary', true)->first() ?? $this->_createDefaultPK($toTable);

            $pivotTable = $diagram->tables()->create([
                'name'     => str()->snake($fromTable->name) . '_' . str()->snake($toTable->name),
                'x'        => $pivotX,
                'y'        => $pivotY,
                'color'    => Helpers::getRandomColor($diagram->style),
                'is_pivot' => true,
            ]);

            $pivotId = $pivotTable->id;

            // Create foreign key columns in pivot table
            $fromColumnPivot = $pivotTable->columns()->create([
                'name'        => str()->snake($fromTable->name) . '_id',
                'type'        => 'bigint',
                'is_foreign'  => true,
                'is_notnull'  => true,
                'order'       => 1,
            ]);

            $toColumnPivot = $pivotTable->columns()->create([
                'name'        => str()->snake($toTable->name) . '_id',
                'type'        => 'bigint',
                'is_foreign'  => true,
                'is_notnull'  => true,
                'order'       => 2,
            ]);

            // Create relationships: pivot -> fromTable and pivot -> toTable
            $relationship1 = Relationship::create([
                'from_table_id'       => $pivotTable->id,
                'to_table_id'         => $fromTable->id,
                'from_column_id'      => $fromColumnPivot->id,
                'to_column_id'        => $fromColumnPrimary->id,
                'type'                => RelationshipType::ONE_TO_MANY,
                'parent_n_n_pivot_id' => $pivotId,
            ]);

            $relationship2 = Relationship::create([
                'from_table_id'       => $pivotTable->id,
                'to_table_id'         => $toTable->id,
                'from_column_id'      => $toColumnPivot->id,
                'to_column_id'        => $toColumnPrimary->id,
                'type'                => RelationshipType::ONE_TO_MANY,
                'parent_n_n_pivot_id' => $pivotId,
            ]);

            return new Collection([$relationship1, $relationship2]);
        });
    }

    /**
     * @param Table $table
     * @return Column
     */
    private function _createDefaultPK(Table $table): Column
    {
        $minOrder = $table->columns()->min('order');

        // Se não há colunas, usar order = 1
        // Se há colunas e minOrder > 1, usar minOrder - 1
        // Se há colunas e minOrder <= 1, usar minOrder / 2 (garante valor menor)
        $order = $minOrder === null
            ? 1
            : ($minOrder > 1 ? $minOrder - 1 : $minOrder / 2);

        return $table->columns()->create([
            'name'        => 'id',
            'type'        => 'bigint',
            'is_primary'  => true,
            'is_notnull'  => true,
            'order'       => $order,
        ]);
    }

    /**
     * @param Table $table
     * @return Column
     */
    private function _createDefaultFK(Table $table, ?string $nameColumnForeignKey = null): Column
    {
        $maxOrder = $table->columns()->max('order');
        $newOrder = Helpers::calculateNextOrder($maxOrder);

        return $table->columns()->create([
            'name'        => $nameColumnForeignKey ?? str()->snake($table->name) . '_id',
            'type'        => 'bigint',
            'is_foreign'  => true,
            'is_notnull'  => false,
            'order'       => $newOrder,
        ]);
    }
}
