<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Criar usuários adicionais
        User::factory(5)->create();

        // Run seeders in the correct order (respecting dependencies)
        $this->call([
            DiagramSeeder::class,
            TableSeeder::class,
            ColumnSeeder::class,
            // RelationshipSeeder::class,
        ]);
    }
}
