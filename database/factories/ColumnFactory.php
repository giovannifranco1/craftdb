<?php

namespace Database\Factories;

use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Column>
 */
class ColumnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['VARCHAR', 'INT', 'BIGINT', 'TEXT', 'DATETIME', 'DATE', 'BOOLEAN', 'DECIMAL', 'FLOAT'];
        $type = fake()->randomElement($types);

        return [
            'table_id' => Table::factory(),
            'name' => fake()->word(),
            'type' => $type,
            'is_primary' => fake()->boolean(10), // 10% chance of being primary
            'is_foreign' => fake()->boolean(20), // 20% chance of being foreign
            'is_notnull' => fake()->boolean(30), // 30% chance of being not null (70% nullable inverted)
            'order' => fake()->numberBetween(1, 20),
            'length' => $this->getLengthForType($type),
            'default_value' => fake()->optional(0.3)->randomElement(['NULL', '0', '1', 'CURRENT_TIMESTAMP']),
            'comment' => fake()->optional(0.4)->sentence(),
        ];
    }

    /**
     * Get appropriate length for the given type.
     */
    private function getLengthForType(string $type): ?int
    {
        return match ($type) {
            'VARCHAR' => fake()->numberBetween(50, 255),
            'CHAR' => fake()->numberBetween(1, 50),
            'DECIMAL' => fake()->numberBetween(5, 15),
            'FLOAT' => fake()->numberBetween(5, 15),
            default => null,
        };
    }

    /**
     * Indicate that the column should be a primary key.
     */
    public function primary(): static
    {
        return $this->state(fn () => [
            'is_primary' => true,
            'is_foreign' => false,
            'is_notnull' => true,
        ]);
    }

    /**
     * Indicate that the column should be a foreign key.
     */
    public function foreign(): static
    {
        return $this->state(fn () => [
            'is_foreign' => true,
            'is_primary' => false,
        ]);
    }

    /**
     * Indicate that the column should not be nullable.
     */
    public function notNullable(): static
    {
        return $this->state(fn () => [
            'is_notnull' => true,
        ]);
    }

    /**
     * Indicate that the column should be nullable.
     */
    public function nullable(): static
    {
        return $this->state(fn () => [
            'is_notnull' => false,
        ]);
    }

    /**
     * Indicate that the column should have a specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn () => [
            'type' => $type,
            'length' => $this->getLengthForType($type),
        ]);
    }
}
