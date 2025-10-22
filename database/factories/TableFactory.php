<?php

namespace Database\Factories;

use App\Models\Diagram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'diagram_id' => Diagram::factory(),
            'name' => fake()->word() . '_' . fake()->randomNumber(3),
            'x' => fake()->numberBetween(0, 1000),
            'y' => fake()->numberBetween(0, 1000),
            'color' => fake()->optional(0.6)->hexColor(),
        ];
    }

    /**
     * Indicate that the table should have a specific color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn () => [
            'color' => $color,
        ]);
    }

    /**
     * Indicate that the table should not have a color.
     */
    public function withoutColor(): static
    {
        return $this->state(fn () => [
            'color' => null,
        ]);
    }

    /**
     * Indicate that the table should be positioned at specific coordinates.
     */
    public function atPosition(int $x, int $y): static
    {
        return $this->state(fn () => [
            'x' => $x,
            'y' => $y,
        ]);
    }
}
