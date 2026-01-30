<?php

namespace Database\Seeders;

use App\Models\Team\Role;
use App\Models\Team\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            // Verifica se já tem roles
            if ($team->roles()->count() === 0) {
                Role::create([
                    'team_id' => $team->id,
                    'code' => 'editor',
                    'name' => 'Editor',
                    'description' => 'Pode criar, editar e visualizar diagramas',
                ]);

                Role::create([
                    'team_id' => $team->id,
                    'code' => 'viewer',
                    'name' => 'Visualizador',
                    'description' => 'Pode apenas visualizar diagramas',
                ]);

                $this->command->info("Roles criadas para o time: {$team->name}");
            } else {
                $this->command->info("Time {$team->name} já possui roles");
            }
        }
    }
}
