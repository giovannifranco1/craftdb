<?php

namespace Database\Seeders;

use App\Models\Column;
use App\Models\Table;
use Illuminate\Database\Seeder;

class ColumnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar colunas para tabelas existentes
        $tables = Table::all();

        if ($tables->isEmpty()) {
            $this->command->warn('Nenhuma tabela encontrada. Execute o TableSeeder primeiro.');
            return;
        }

        // Criar colunas para cada tabela
        foreach ($tables as $table) {
            $columnCount = fake()->numberBetween(3, 8);

            // Sempre criar uma coluna ID como chave primária
            Column::factory()
                ->primary()
                ->ofType('BIGINT')
                ->notNullable()
                ->create([
                    'table_id' => $table->id,
                    'name' => 'id',
                    'order' => 1,
                ]);

            // Criar colunas regulares
            for ($i = 2; $i <= $columnCount; $i++) {
                Column::factory()
                    ->create([
                        'table_id' => $table->id,
                        'order' => $i,
                    ]);
            }

            // Criar algumas colunas específicas baseadas no nome da tabela
            $this->createSpecificColumns($table);
        }
    }

    /**
     * Criar colunas específicas baseadas no nome da tabela.
     */
    private function createSpecificColumns(Table $table): void
    {
        $tableName = strtolower($table->name);

        switch ($tableName) {
            case 'users':
                $this->createUserColumns($table);
                break;
            case 'posts':
                $this->createPostColumns($table);
                break;
            case 'categories':
                $this->createCategoryColumns($table);
                break;
        }
    }

    /**
     * Criar colunas específicas para tabela users.
     */
    private function createUserColumns(Table $table): void
    {
        $columns = [
            ['name' => 'name', 'type' => 'VARCHAR', 'length' => 255, 'is_notnull' => true],
            ['name' => 'email', 'type' => 'VARCHAR', 'length' => 255, 'is_notnull' => true],
            ['name' => 'email_verified_at', 'type' => 'DATETIME', 'is_notnull' => false],
            ['name' => 'password', 'type' => 'VARCHAR', 'length' => 255, 'is_notnull' => true],
            ['name' => 'remember_token', 'type' => 'VARCHAR', 'length' => 100, 'is_notnull' => false],
            ['name' => 'created_at', 'type' => 'DATETIME', 'is_notnull' => true],
            ['name' => 'updated_at', 'type' => 'DATETIME', 'is_notnull' => true],
        ];

        $this->createColumnsFromArray($table, $columns);
    }

    /**
     * Criar colunas específicas para tabela posts.
     */
    private function createPostColumns(Table $table): void
    {
        $columns = [
            ['name' => 'title', 'type' => 'VARCHAR', 'length' => 255, 'is_notnull' => true],
            ['name' => 'content', 'type' => 'TEXT', 'is_notnull' => false],
            ['name' => 'user_id', 'type' => 'BIGINT', 'is_foreign' => true, 'is_notnull' => true],
            ['name' => 'category_id', 'type' => 'BIGINT', 'is_foreign' => true, 'is_notnull' => false],
            ['name' => 'published_at', 'type' => 'DATETIME', 'is_notnull' => false],
            ['name' => 'created_at', 'type' => 'DATETIME', 'is_notnull' => true],
            ['name' => 'updated_at', 'type' => 'DATETIME', 'is_notnull' => true],
        ];

        $this->createColumnsFromArray($table, $columns);
    }

    /**
     * Criar colunas específicas para tabela categories.
     */
    private function createCategoryColumns(Table $table): void
    {
        $columns = [
            ['name' => 'name', 'type' => 'VARCHAR', 'length' => 100, 'is_notnull' => true],
            ['name' => 'slug', 'type' => 'VARCHAR', 'length' => 100, 'is_notnull' => true],
            ['name' => 'description', 'type' => 'TEXT', 'is_notnull' => false],
            ['name' => 'created_at', 'type' => 'DATETIME', 'is_notnull' => true],
            ['name' => 'updated_at', 'type' => 'DATETIME', 'is_notnull' => true],
        ];

        $this->createColumnsFromArray($table, $columns);
    }

    /**
     * Criar colunas a partir de um array de configurações.
     */
    private function createColumnsFromArray(Table $table, array $columns): void
    {
        foreach ($columns as $index => $columnData) {
            Column::factory()
                ->create(array_merge([
                    'table_id' => $table->id,
                    'order' => $index + 1,
                ], $columnData));
        }
    }
}
