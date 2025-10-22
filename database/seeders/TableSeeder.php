<?php

namespace Database\Seeders;

use App\Models\Table;
use App\Models\Diagram;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar tabelas para diagramas existentes
        $diagrams = Diagram::all();

        if ($diagrams->isEmpty()) {
            $this->command->warn('Nenhum diagrama encontrado. Execute o DiagramSeeder primeiro.');
            return;
        }

        // Criar 2-8 tabelas por diagrama
        foreach ($diagrams as $diagram) {
            $tableCount = fake()->numberBetween(2, 8);

            Table::factory()
                ->count($tableCount)
                ->create([
                    'diagram_id' => $diagram->id,
                ]);

            // Criar algumas tabelas com cores específicas
            Table::factory()
                ->count(2)
                ->withColor('#3B82F6') // Azul
                ->create([
                    'diagram_id' => $diagram->id,
                ]);

            Table::factory()
                ->count(1)
                ->withColor('#10B981') // Verde
                ->create([
                    'diagram_id' => $diagram->id,
                ]);
        }

        // Criar algumas tabelas em posições específicas para demonstrar layout
        $firstDiagram = $diagrams->first();
        if ($firstDiagram) {
            Table::factory()
                ->atPosition(100, 100)
                ->create([
                    'diagram_id' => $firstDiagram->id,
                    'name' => 'users',
                ]);

            Table::factory()
                ->atPosition(400, 100)
                ->create([
                    'diagram_id' => $firstDiagram->id,
                    'name' => 'posts',
                ]);

            Table::factory()
                ->atPosition(100, 300)
                ->create([
                    'diagram_id' => $firstDiagram->id,
                    'name' => 'categories',
                ]);
        }
    }
}
