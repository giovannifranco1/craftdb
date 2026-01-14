<?php

namespace Database\Factories;

use App\Models\Diagram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
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
            'name' => fake()->words(2, true),
            'x' => fake()->numberBetween(0, 1000),
            'y' => fake()->numberBetween(0, 1000),
            'width' => fake()->numberBetween(200, 800),
            'height' => fake()->numberBetween(150, 600),
            'color' => fake()->optional(0.6)->hexColor(),
        ];
    }

    /**
     * Indicate that the area should have a specific color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn () => [
            'color' => $color,
        ]);
    }

    /**
     * Indicate that the area should not have a color.
     */
    public function withoutColor(): static
    {
        return $this->state(fn () => [
            'color' => null,
        ]);
    }

    /**
     * Indicate that the area should be positioned at specific coordinates.
     */
    public function atPosition(int $x, int $y): static
    {
        return $this->state(fn () => [
            'x' => $x,
            'y' => $y,
        ]);
    }

    /**
     * Indicate that the area should have specific dimensions.
     */
    public function withDimensions(int $width, int $height): static
    {
        return $this->state(fn () => [
            'width' => $width,
            'height' => $height,
        ]);
    }
}