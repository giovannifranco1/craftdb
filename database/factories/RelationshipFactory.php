<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\Column;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Relationship>
 */
class RelationshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromTable = Table::factory()->create();
        $toTable = Table::factory()->create();

        $fromColumn = Column::factory()->create(['table_id' => $fromTable->id]);
        $toColumn = Column::factory()->create(['table_id' => $toTable->id]);

        return [
            'from_table_id' => $fromTable->id,
            'from_column_id' => $fromColumn->id,
            'to_table_id' => $toTable->id,
            'to_column_id' => $toColumn->id,
            'type' => fake()->randomElement(['1:1', '1:n', 'n:n']),
            'label' => fake()->optional(0.5)->word(),
        ];
    }

    /**
     * Indicate that the relationship should be one-to-one.
     */
    public function oneToOne(): static
    {
        return $this->state(fn () => [
            'type' => '1:1',
        ]);
    }

    /**
     * Indicate that the relationship should be one-to-many.
     */
    public function oneToMany(): static
    {
        return $this->state(fn () => [
            'type' => '1:n',
        ]);
    }

    /**
     * Indicate that the relationship should be many-to-many.
     */
    public function manyToMany(): static
    {
        return $this->state(fn () => [
            'type' => 'n:n',
        ]);
    }

    /**
     * Indicate that the relationship should have a specific label.
     */
    public function withLabel(string $label): static
    {
        return $this->state(fn () => [
            'label' => $label,
        ]);
    }

    /**
     * Indicate that the relationship should not have a label.
     */
    public function withoutLabel(): static
    {
        return $this->state(fn () => [
            'label' => null,
        ]);
    }

    /**
     * Create a relationship between specific tables and columns.
     */
    public function between(Table $fromTable, Column $fromColumn, Table $toTable, Column $toColumn): static
    {
        return $this->state(fn () => [
            'from_table_id' => $fromTable->id,
            'from_column_id' => $fromColumn->id,
            'to_table_id' => $toTable->id,
            'to_column_id' => $toColumn->id,
        ]);
    }
}
