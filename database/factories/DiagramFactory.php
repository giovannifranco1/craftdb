<?php

namespace Database\Factories;

use App\Models\Team\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Diagram>
 */
class DiagramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);
        $slug = str()->slug($name);

        return [
            'team_id' => Team::factory(),
            'name' => fake()->words(2, true),
            'slug' => $slug,
            'description' => fake()->optional(0.7)->sentence(),
        ];
    }

    /**
     * Indicate that the diagram should have a description.
     */
    public function withDescription(): static
    {
        return $this->state(fn () => [
            'description' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the diagram should not have a description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn () => [
            'description' => null,
        ]);
    }
}
