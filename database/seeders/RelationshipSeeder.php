<?php

namespace Database\Seeders;

use App\Models\Relationship;
use App\Models\Table;
use Illuminate\Database\Seeder;

class RelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar relacionamentos para tabelas existentes
        $tables = Table::with('columns')->get();

        if ($tables->isEmpty()) {
            $this->command->warn('Nenhuma tabela encontrada. Execute o TableSeeder primeiro.');
            return;
        }

        // Criar relacionamentos aleatórios entre tabelas
        $this->createRandomRelationships($tables);

        // Criar relacionamentos específicos para tabelas conhecidas
        $this->createSpecificRelationships($tables);
    }

    /**
     * Criar relacionamentos aleatórios entre tabelas.
     */
    private function createRandomRelationships($tables): void
    {
        // Criar 1-3 relacionamentos por tabela
        foreach ($tables as $table) {
            $relationshipCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $relationshipCount; $i++) {
                // Escolher uma tabela de destino diferente
                $toTable = $tables->where('id', '!=', $table->id)->random();

                // Escolher colunas aleatórias
                $fromColumn = $table->columns->random();
                $toColumn = $toTable->columns->random();

                // Criar relacionamento
                Relationship::factory()
                    ->between($table, $fromColumn, $toTable, $toColumn)
                    ->create();
            }
        }
    }

    /**
     * Criar relacionamentos específicos para tabelas conhecidas.
     */
    private function createSpecificRelationships($tables): void
    {
        $usersTable = $tables->where('name', 'users')->first();
        $postsTable = $tables->where('name', 'posts')->first();
        $categoriesTable = $tables->where('name', 'categories')->first();

        // Relacionamento users -> posts (1:n)
        if ($usersTable && $postsTable) {
            $userIdColumn = $usersTable->columns->where('name', 'id')->first();
            $postUserIdColumn = $postsTable->columns->where('name', 'user_id')->first();

            if ($userIdColumn && $postUserIdColumn) {
                Relationship::factory()
                    ->oneToMany()
                    ->withLabel('author')
                    ->between($usersTable, $userIdColumn, $postsTable, $postUserIdColumn)
                    ->create();
            }
        }

        // Relacionamento categories -> posts (1:n)
        if ($categoriesTable && $postsTable) {
            $categoryIdColumn = $categoriesTable->columns->where('name', 'id')->first();
            $postCategoryIdColumn = $postsTable->columns->where('name', 'category_id')->first();

            if ($categoryIdColumn && $postCategoryIdColumn) {
                Relationship::factory()
                    ->oneToMany()
                    ->withLabel('category')
                    ->between($categoriesTable, $categoryIdColumn, $postsTable, $postCategoryIdColumn)
                    ->create();
            }
        }

        // Criar alguns relacionamentos many-to-many para demonstração
        $this->createManyToManyRelationships($tables);
    }

    /**
     * Criar relacionamentos many-to-many para demonstração.
     */
    private function createManyToManyRelationships($tables): void
    {
        // Pegar algumas tabelas aleatórias para criar relacionamentos n:n
        $randomTables = $tables->random(min(4, $tables->count()));

        for ($i = 0; $i < $randomTables->count() - 1; $i += 2) {
            $table1 = $randomTables[$i];
            $table2 = $randomTables[$i + 1] ?? $randomTables[0];

            $column1 = $table1->columns->random();
            $column2 = $table2->columns->random();

            Relationship::factory()
                ->manyToMany()
                ->withLabel('many_to_many')
                ->between($table1, $column1, $table2, $column2)
                ->create();
        }
    }
}
