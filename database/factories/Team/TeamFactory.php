<?php

namespace Database\Factories\Team;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // fake()->name() pode gerar "Prof."/"Freeman", colidindo com asserts de texto.
            'name' => 'Workspace '.str()->lower(str()->random(8)),
            'user_id' => User::factory()
        ];
    }
}
