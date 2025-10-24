<?php

namespace Database\Seeders;

use App\Models\Diagram;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DiagramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar alguns diagramas para usuários existentes
        $teams = Team::all();

        if ($teams->isEmpty()) {
            // Se não há usuários, criar um usuário primeiro
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);

            $team = Team::factory()->create([
                'user_id' => $user->id
            ]);

            $teams = collect([$team]);
        }

        // Criar 3-5 diagramas por usuário
        foreach ($teams as $team) {
            Diagram::factory()
                ->count(fake()->numberBetween(3, 5))
                ->create([
                    'team_id' => $team->id,
                ]);
        }

        // Criar alguns diagramas adicionais com descrições
        Diagram::factory()
            ->count(2)
            ->withDescription()
            ->create([
                'team_id' => $team->first()->id,
            ]);
    }
}
