<?php

namespace App\Actions\Diagram;

use App\Enums\RelationshipType;
use App\Models\Column;
use App\Models\Relationship;

/**
 * Creates a relationship using existing foreign key columns
 *
 * @phpstan-type DTORelationshipFK array{
 *   ownerTableId: int,
 *   ownerColumnId: int,
 *   childTableId: int,
 *   foreignKeyColumnId: int,
 *   type?: RelationshipType
 * }
 */
class AddRelationshipWithExistingForeignKey
{
    /**
     * Create a relationship with existing foreign key
     *
     * @param DTORelationshipFK $data
     * @return Relationship
     */
    public function exec(array $data): Relationship
    {
        $this->validate($data);

        $ownerColumn = Column::findOrFail($data['ownerColumnId']);
        $foreignKeyColumn = Column::findOrFail($data['foreignKeyColumnId']);

        // Mark column as foreign key if not already marked
        if (!$foreignKeyColumn->is_foreign) {
            $foreignKeyColumn->update(['is_foreign' => true]);
        }

        // Get relationship type (default to MANY_TO_ONE)
        $type = $data['type'] ?? RelationshipType::MANY_TO_ONE;

        // Create relationship: owner (from) -> child (to)
        // Example: users (from) -> posts (to) where posts.user_id references users.id
        return Relationship::create([
            'from_table_id' => $data['ownerTableId'],       // Parent/Owner table
            'from_column_id' => $ownerColumn->id,           // Primary key
            'to_table_id' => $data['childTableId'],         // Child table
            'to_column_id' => $foreignKeyColumn->id,        // Foreign key
            'type' => $type,
        ]);
    }

    /**
     * @param DTORelationshipFK $data
     */
    private function validate(array $data): void
    {
        validator($data, [
            'ownerTableId' => ['required', 'integer', 'exists:table,id'],
            'ownerColumnId' => ['required', 'integer', 'exists:column,id'],
            'childTableId' => ['required', 'integer', 'exists:table,id'],
            'foreignKeyColumnId' => ['required', 'integer', 'exists:column,id'],
        ])->validate();
    }
}
