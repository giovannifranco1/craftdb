<?php

namespace Database\Seeders;

use App\Models\Diagram;
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
        $users = User::all();

        if ($users->isEmpty()) {
            // Se não há usuários, criar um usuário primeiro
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
            $users = collect([$user]);
        }

        // Criar 3-5 diagramas por usuário
        foreach ($users as $user) {
            Diagram::factory()
                ->count(fake()->numberBetween(3, 5))
                ->create([
                    'user_id' => $user->id,
                ]);
        }

        // Criar alguns diagramas adicionais com descrições
        Diagram::factory()
            ->count(2)
            ->withDescription()
            ->create([
                'user_id' => $users->first()->id,
            ]);
    }
}
