<?php

namespace Tests\Feature\Actions\Parse;

use App\Actions\Diagram\AddRelationshipWithExistingForeignKey;
use App\Actions\Parse\ParseLaravelMigration;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Relationship;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ReflectionClass;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Create and authenticate a user with a team
    /** @disregard */
    $this->user = User::factory()->create();

    /** @disregard */
    $this->team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    /** @disregard */
    $this->diagram = Diagram::factory()->create([
        'name' => 'Test Diagram',
        'style' => 'neutral',
        'database' => 'mysql',
        'team_id' => $this->team->id,
    ]);

    /** @disregard */
    $this->parser = new ParseLaravelMigration(
        app(AddRelationshipWithExistingForeignKey::class)
    );
});

describe('Schema::create() extraction', function (): void {
    it('can extract single Schema::create block', function () {
        $content = <<<'PHP'
            <?php
            use Illuminate\Database\Migrations\Migration;
            use Illuminate\Database\Schema\Blueprint;
            use Illuminate\Support\Facades\Schema;

            return new class extends Migration
            {
                public function up(): void
                {
                    Schema::create('users', function (Blueprint $table) {
                        $table->id();
                        $table->string('name');
                    });
                }
            };
        PHP;

        /** @disregard */
        $reflection = new ReflectionClass($this->parser);
        $method = $reflection->getMethod('extractSchemaCreateBlocks');
        /** @disregard */
        $blocks = $method->invoke($this->parser, $content);

        expect($blocks)->toHaveCount(1)
            ->and($blocks[0]['table'])->toBe('users')
            ->and($blocks[0]['blueprint'])->toContain('$table->id()')
            ->and($blocks[0]['blueprint'])->toContain('$table->string(\'name\')');
    });

    it('can extract multiple Schema::create blocks', function () {
        $content = <<<'PHP'
                Schema::create('users', function (Blueprint $table) {
                    $table->id();
                });

                Schema::create('posts', function (Blueprint $table) {
                    $table->id();
            });
        PHP;
        /** @disregard */
        $reflection = new ReflectionClass($this->parser);
        $method = $reflection->getMethod('extractSchemaCreateBlocks');

        /** @disregard */
        $blocks = $method->invoke($this->parser, $content);

        expect($blocks)->toHaveCount(2)
            ->and($blocks[0]['table'])->toBe('users')
            ->and($blocks[1]['table'])->toBe('posts');
    });
});

describe('Column parsing', function (): void {
    it('can parse id column', function () {
        $file = createMigrationFile('users', '$table->id();');

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'users')
            ->first();

        expect($table)->not->toBeNull();

        $column = Column::where('table_id', $table->id)
            ->where('name', 'id')
            ->first();

        expect($column)->not->toBeNull()
            ->and($column->type)->toBe('bigint')
            ->and((bool) $column->is_primary)->toBeTrue()
            ->and((bool) $column->is_notnull)->toBeTrue();
    });

    it('can parse string column', function () {
        $file = createMigrationFile('users', "\$table->string('name');");

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)->first();
        $column = Column::where('table_id', $table->id)
            ->where('name', 'name')
            ->first();

        expect($column)->not->toBeNull()
            ->and($column->type)->toBe('varchar')
            ->and($column->length)->toBe(255)
            ->and((bool) $column->is_notnull)->toBeTrue();
    });

    it('can parse string column with custom length', function () {
        $file = createMigrationFile('users', "\$table->string('email', 100);");

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)->first();
        $column = Column::where('table_id', $table->id)
            ->where('name', 'email')
            ->first();

        expect($column)->not->toBeNull()
            ->and($column->type)->toBe('varchar')
            ->and($column->length)->toBe(100);
    });

    it('can parse nullable column', function () {
        $file = createMigrationFile('users', "\$table->string('bio')->nullable();");

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)->first();
        $column = Column::where('table_id', $table->id)
            ->where('name', 'bio')
            ->first();

        expect($column)->not->toBeNull()
            ->and((bool) $column->is_notnull)->toBeFalse();
    });

    it('can parse column with default value', function () {
        $file = createMigrationFile('posts', "\$table->boolean('published')->default(false);");

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)->first();
        $column = Column::where('table_id', $table->id)
            ->where('name', 'published')
            ->first();

        expect($column)->not->toBeNull()
            ->and($column->default_value)->toBe('false');
    });

    it('can parse column with comment', function () {
        $file = createMigrationFile('users', "\$table->string('name')->comment('User name');");

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)->first();
        $column = Column::where('table_id', $table->id)
            ->where('name', 'name')
            ->first();

        expect($column)->not->toBeNull()
            ->and($column->comment)->toBe('User name');
    });
});

describe('Special methods', function (): void {
    it('can parse timestamps()', function () {
        $file = createMigrationFile('users', '$table->timestamps();');

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)->first();

        $createdAt = Column::where('table_id', $table->id)
            ->where('name', 'created_at')
            ->first();

        $updatedAt = Column::where('table_id', $table->id)
            ->where('name', 'updated_at')
            ->first();

        expect($createdAt)->not->toBeNull()
            ->and($createdAt->type)->toBe('timestamp')
            ->and((bool) $createdAt->is_notnull)->toBeFalse()
            ->and($updatedAt)->not->toBeNull()
            ->and($updatedAt->type)->toBe('timestamp')
            ->and((bool) $updatedAt->is_notnull)->toBeFalse();
    });

    it('can parse softDeletes()', function () {
        $file = createMigrationFile('posts', '$table->softDeletes();');

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)->first();

        $deletedAt = Column::where('table_id', $table->id)
            ->where('name', 'deleted_at')
            ->first();

        expect($deletedAt)->not->toBeNull()
            ->and($deletedAt->type)->toBe('timestamp')
            ->and((bool) $deletedAt->is_notnull)->toBeFalse();
    });
});

describe('Foreign key detection', function (): void {
    it('can detect explicit foreign key', function () {
        /** @disregard */
        $reflection = new ReflectionClass($this->parser);
        $method = $reflection->getMethod('extractForeignKeysFromBlueprint');

        $foreignKeys = [];

        /** @disregard */
        $method->invokeArgs($this->parser, ["\$table->unsignedBigInteger('user_id');\n\$table->foreign('user_id')->references('id')->on('users');", 'posts', &$foreignKeys]);

        expect($foreignKeys)->toHaveCount(1)
            ->and($foreignKeys[0]['table'])->toBe('posts')
            ->and($foreignKeys[0]['column'])->toBe('user_id')
            ->and($foreignKeys[0]['ref_table'])->toBe('users')
            ->and($foreignKeys[0]['ref_column'])->toBe('id');
    });

    it('can detect foreignId with constrained', function () {
        $content = "\$table->foreignId('user_id')->constrained();";

        /** @disregard */
        $reflection = new ReflectionClass($this->parser);
        $method = $reflection->getMethod('extractForeignKeysFromBlueprint');

        $foreignKeys = [];
        /** @disregard */
        $method->invokeArgs($this->parser, [$content, 'posts', &$foreignKeys]);

        expect($foreignKeys)->toHaveCount(1)
            ->and($foreignKeys[0]['table'])->toBe('posts')
            ->and($foreignKeys[0]['column'])->toBe('user_id')
            ->and($foreignKeys[0]['ref_table'])->toBe('users')
            ->and($foreignKeys[0]['ref_column'])->toBe('id');
    });

    it('can detect foreignId with explicit table', function () {
        $content = "\$table->foreignId('author_id')->constrained('users');";

        /** @disregard */
        $reflection = new ReflectionClass($this->parser);
        $method = $reflection->getMethod('extractForeignKeysFromBlueprint');

        $foreignKeys = [];
        /** @disregard */
        $method->invokeArgs($this->parser, [$content, 'posts', &$foreignKeys]);

        expect($foreignKeys)->toHaveCount(1)
            ->and($foreignKeys[0]['table'])->toBe('posts')
            ->and($foreignKeys[0]['column'])->toBe('author_id')
            ->and($foreignKeys[0]['ref_table'])->toBe('users')
            ->and($foreignKeys[0]['ref_column'])->toBe('id');
    });

    it('can detect foreignId with indexName parameter', function () {
        $content = "\$table->foreignId('diagram_id')->constrained('diagram', indexName: 'diagram_id_foreign');";

        /** @disregard */
        $reflection = new ReflectionClass($this->parser);
        $method = $reflection->getMethod('extractForeignKeysFromBlueprint');

        $foreignKeys = [];
        /** @disregard */
        $method->invokeArgs($this->parser, [$content, 'table', &$foreignKeys]);

        expect($foreignKeys)->toHaveCount(1)
            ->and($foreignKeys[0]['table'])->toBe('table')
            ->and($foreignKeys[0]['column'])->toBe('diagram_id')
            ->and($foreignKeys[0]['ref_table'])->toBe('diagram')
            ->and($foreignKeys[0]['ref_column'])->toBe('id');
    });
});

describe('Relationship creation', function (): void {
    it('can create relationship from foreign key', function () {
        $usersFile = createMigrationFile('users', '$table->id();');
        $postsFile = createMigrationFile('posts', <<<'PHP'
            $table->id();
            $table->foreignId('user_id')->constrained();
        PHP);

        /** @disregard */
        $this->parser->exec([$usersFile, $postsFile], $this->diagram);

        /** @disregard */
        $usersTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'users')
            ->first();

        /** @disregard */
        $postsTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'posts')
            ->first();

        expect($usersTable)->not->toBeNull()
            ->and($postsTable)->not->toBeNull();

        $relationship = Relationship::where('from_table_id', $usersTable->id)
            ->where('to_table_id', $postsTable->id)
            ->first();

        expect($relationship)->not->toBeNull();
    });

    it('can create multiple relationships', function () {
        $usersFile = createMigrationFile('users', '$table->id();');
        $postsFile = createMigrationFile('posts', '$table->id();');
        $commentsFile = createMigrationFile('comments', <<<'PHP'
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('post_id')->constrained();
        PHP);

        /** @disregard */
        $this->parser->exec([$usersFile, $postsFile, $commentsFile], $this->diagram);

        $relationships = Relationship::whereHas('toTable', function ($query) {
            $query->where('name', 'comments');
        })->get();

        expect($relationships)->toHaveCount(2);
    });

    it('can create relationship with indexName parameter', function () {
        $diagramFile = createMigrationFile('diagram', '$table->id();');
        $tableFile = createMigrationFile('table', <<<'PHP'
            $table->id();
            $table->foreignId('diagram_id')->constrained('diagram', indexName: 'diagram_id_foreign');
        PHP);

        /** @disregard */
        $this->parser->exec([$diagramFile, $tableFile], $this->diagram);

        /** @disregard */
        $diagramTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'diagram')
            ->first();

        /** @disregard */
        $tableTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'table')
            ->first();

        expect($diagramTable)->not->toBeNull()
            ->and($tableTable)->not->toBeNull();

        $relationship = Relationship::where('from_table_id', $diagramTable->id)
            ->where('to_table_id', $tableTable->id)
            ->first();

        expect($relationship)->not->toBeNull();
    });
});

describe('Full integration test', function (): void {
    it('can parse complete migration files', function () {
        $files = [
            UploadedFile::fake()->createWithContent(
                '2024_01_01_create_users_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_02_create_posts_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000002_create_posts_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_03_create_comments_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000003_create_comments_table.php')
            ),
        ];

        /** @disregard */
        $this->parser->exec($files, $this->diagram);

        // Check tables created
        /** @disregard */
        $tables = Table::where('diagram_id', $this->diagram->id)->get();
        expect($tables)->toHaveCount(3);

        // Check users table
        $usersTable = $tables->firstWhere('name', 'users');
        expect($usersTable)->not->toBeNull();

        $usersColumns = Column::where('table_id', $usersTable->id)->get();
        expect($usersColumns)->toHaveCount(7); // id, name, email, email_verified_at, password, created_at, updated_at (rememberToken not yet implemented)

        // Check posts table
        $postsTable = $tables->firstWhere('name', 'posts');
        expect($postsTable)->not->toBeNull();

        $postsColumns = Column::where('table_id', $postsTable->id)->get();
        expect($postsColumns)->toHaveCount(8); // id, user_id, title, content, published, created_at, updated_at, deleted_at

        // Check comments table
        $commentsTable = $tables->firstWhere('name', 'comments');
        expect($commentsTable)->not->toBeNull();

        $commentsColumns = Column::where('table_id', $commentsTable->id)->get();
        expect($commentsColumns)->toHaveCount(6); // id, post_id, user_id, body, created_at, updated_at

        // Check relationships
        $relationships = Relationship::whereIn('from_table_id', $tables->pluck('id'))
            ->orWhereIn('to_table_id', $tables->pluck('id'))
            ->get();

        expect($relationships)->toHaveCount(3); // posts->users, comments->posts, comments->users
    });

    it('can parse foreign keys with index() and enum columns', function () {
        $files = [
            UploadedFile::fake()->createWithContent(
                '2024_01_01_create_users_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_02_create_posts_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000002_create_posts_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_03_create_comments_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000003_create_comments_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_04_create_relationships_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000004_create_relationships_table.php')
            ),
        ];

        /** @disregard */
        $this->parser->exec($files, $this->diagram);

        // Check tables created
        /** @disregard */
        $tables = Table::where('diagram_id', $this->diagram->id)->get();
        expect($tables)->toHaveCount(4);

        // Check relationships table
        $relationshipsTable = $tables->firstWhere('name', 'relationships');
        expect($relationshipsTable)->not->toBeNull();

        $relationshipsColumns = Column::where('table_id', $relationshipsTable->id)->get();

        // Should have: id, from_user_id, to_user_id, from_post_id, to_post_id, related_comment_id, type, label, created_at, updated_at
        expect($relationshipsColumns)->toHaveCount(10);

        // Check enum column was created
        $typeColumn = $relationshipsColumns->firstWhere('name', 'type');
        expect($typeColumn)->not->toBeNull()
            ->and($typeColumn->type)->toBe('enum');

        // Check foreign keys were created correctly
        $fromUserIdColumn = $relationshipsColumns->firstWhere('name', 'from_user_id');
        expect($fromUserIdColumn)->not->toBeNull()
            ->and((bool) $fromUserIdColumn->is_foreign)->toBeTrue();

        $toUserIdColumn = $relationshipsColumns->firstWhere('name', 'to_user_id');
        expect($toUserIdColumn)->not->toBeNull()
            ->and((bool) $toUserIdColumn->is_foreign)->toBeTrue();

        // Check relationships were created (5 FKs in relationships table)
        $relationships = Relationship::whereHas('toTable', function ($query) {
            $query->where('name', 'relationships');
        })->get();

        expect($relationships)->toHaveCount(5); // from_user_id, to_user_id, from_post_id, to_post_id, related_comment_id
    });

    it('can parse multi-line foreign keys and enum', function () {
        $files = [
            UploadedFile::fake()->createWithContent(
                '2024_01_01_create_users_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_05_create_tags_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000005_create_tags_table.php')
            ),
        ];

        /** @disregard */
        $this->parser->exec($files, $this->diagram);

        // Check tables created
        /** @disregard */
        $tables = Table::where('diagram_id', $this->diagram->id)->get();
        expect($tables)->toHaveCount(2);

        // Check tags table
        $tagsTable = $tables->firstWhere('name', 'tags');
        expect($tagsTable)->not->toBeNull();

        $tagsColumns = Column::where('table_id', $tagsTable->id)->get();

        // Should have: id, user_id, name, status, created_at, updated_at
        expect($tagsColumns)->toHaveCount(6);

        // Check enum column with multi-value array
        $statusColumn = $tagsColumns->firstWhere('name', 'status');
        expect($statusColumn)->not->toBeNull()
            ->and($statusColumn->type)->toBe('enum');

        // Check multi-line foreign key was created correctly
        $userIdColumn = $tagsColumns->firstWhere('name', 'user_id');
        expect($userIdColumn)->not->toBeNull()
            ->and((bool) $userIdColumn->is_foreign)->toBeTrue();

        // Check relationship was created
        $usersTable = $tables->firstWhere('name', 'users');
        $relationship = Relationship::where('from_table_id', $usersTable->id)
            ->where('to_table_id', $tagsTable->id)
            ->first();

        expect($relationship)->not->toBeNull();
    });
});

describe('Advanced migration scenarios', function (): void {
    it('can parse self-referencing foreign key and softDeletes', function () {
        $file = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_categories_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000006_create_categories_table.php')
        );

        /** @disregard */
        $this->parser->exec([$file], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'categories')
            ->first();

        expect($table)->not->toBeNull();

        $columns = Column::where('table_id', $table->id)->get();

        // id, parent_id, name, slug, description, status, order, deleted_at, created_at, updated_at
        expect($columns)->toHaveCount(10);

        // Check self-referencing FK
        $parentColumn = $columns->firstWhere('name', 'parent_id');
        expect($parentColumn)->not->toBeNull()
            ->and((bool) $parentColumn->is_foreign)->toBeTrue();

        // Check enum
        $statusColumn = $columns->firstWhere('name', 'status');
        expect($statusColumn)->not->toBeNull()
            ->and($statusColumn->type)->toBe('enum');

        // Check softDeletes
        $deletedAtColumn = $columns->firstWhere('name', 'deleted_at');
        expect($deletedAtColumn)->not->toBeNull()
            ->and($deletedAtColumn->type)->toBe('timestamp');

        // Check self-referencing relationship
        $relationship = Relationship::where('from_table_id', $table->id)
            ->where('to_table_id', $table->id)
            ->first();
        expect($relationship)->not->toBeNull();
    });

    it('can parse UUID primary key and JSON columns', function () {
        $usersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_users_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
        );

        $categoriesFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_categories_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000006_create_categories_table.php')
        );

        $productsFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_products_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000007_create_products_table.php')
        );

        /** @disregard */
        $this->parser->exec([$usersFile, $categoriesFile, $productsFile], $this->diagram);

        /** @disregard */
        $table = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'products')
            ->first();

        expect($table)->not->toBeNull();

        $columns = Column::where('table_id', $table->id)->get();

        // id, user_id, category_id, name, description, price, stock, is_active, metadata, published_at, created_at, updated_at
        expect($columns)->toHaveCount(12);

        // Check UUID primary
        $idColumn = $columns->firstWhere('name', 'id');
        expect($idColumn)->not->toBeNull()
            ->and($idColumn->type)->toBe('char')
            ->and((bool) $idColumn->is_primary)->toBeTrue();

        // Check JSON column
        $metadataColumn = $columns->firstWhere('name', 'metadata');
        expect($metadataColumn)->not->toBeNull()
            ->and($metadataColumn->type)->toBe('json');

        // Check decimal column
        $priceColumn = $columns->firstWhere('name', 'price');
        expect($priceColumn)->not->toBeNull()
            ->and($priceColumn->type)->toBe('decimal');

        // Check boolean
        $isActiveColumn = $columns->firstWhere('name', 'is_active');
        expect($isActiveColumn)->not->toBeNull()
            ->and($isActiveColumn->type)->toBe('tinyint');

        // Check relationships (user_id, category_id)
        $relationships = Relationship::whereHas('toTable', function ($query) {
            $query->where('name', 'products');
        })->get();
        expect($relationships)->toHaveCount(2);
    });

    it('can parse old-style foreign key syntax', function () {
        /** @var TestCase $this */
        $usersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_users_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
        );

        $ordersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_orders_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000008_create_orders_table.php')
        );

        /** @disregard */
        $this->parser->exec([$usersFile, $ordersFile], $this->diagram);

        /** @disregard */
        $ordersTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'orders')
            ->first();

        expect($ordersTable)->not->toBeNull();

        // Check customer_id column was created
        $customerIdColumn = Column::where('table_id', $ordersTable->id)
            ->where('name', 'customer_id')
            ->first();

        expect($customerIdColumn)->not->toBeNull()
            ->and($customerIdColumn->type)->toBe('bigint')
            ->and((bool) $customerIdColumn->is_foreign)->toBeTrue();

        // Check old-style foreign key created relationship
        $usersTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'users')
            ->first();

        $relationship = Relationship::where('from_table_id', $usersTable->id)
            ->where('to_table_id', $ordersTable->id)
            ->first();

        expect($relationship)->not->toBeNull();
    });

    it('can parse foreignUuid with cascadeOnDelete', function () {
        $usersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_users_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
        );

        $categoriesFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_categories_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000006_create_categories_table.php')
        );

        $productsFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_products_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000007_create_products_table.php')
        );

        $ordersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_orders_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000008_create_orders_table.php')
        );

        $orderItemsFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_order_items_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000009_create_order_items_table.php')
        );

        /** @disregard */
        $this->parser->exec([$usersFile, $categoriesFile, $productsFile, $ordersFile, $orderItemsFile], $this->diagram);

        /** @disregard */
        $orderItemsTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'order_items')
            ->first();

        expect($orderItemsTable)->not->toBeNull();

        // Check foreignUuid column
        $productIdColumn = Column::where('table_id', $orderItemsTable->id)
            ->where('name', 'product_id')
            ->first();

        expect($productIdColumn)->not->toBeNull()
            ->and($productIdColumn->type)->toBe('char')
            ->and((bool) $productIdColumn->is_foreign)->toBeTrue();

        // Check relationships (order_id, product_id)
        $relationships = Relationship::whereHas('toTable', function ($query) {
            $query->where('name', 'order_items');
        })->get();

        expect($relationships)->toHaveCount(2);
    });

    it('can parse multiple foreign keys to same table', function () {
        $usersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_users_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
        );

        $categoriesFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_categories_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000006_create_categories_table.php')
        );

        $productsFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_products_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000007_create_products_table.php')
        );

        $reviewsFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_reviews_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000010_create_reviews_table.php')
        );

        /** @disregard */
        $this->parser->exec([$usersFile, $categoriesFile, $productsFile, $reviewsFile], $this->diagram);

        /** @disregard */
        $reviewsTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'reviews')
            ->first();

        expect($reviewsTable)->not->toBeNull();

        $columns = Column::where('table_id', $reviewsTable->id)->get();

        // id, user_id, product_id, rating, title, comment, is_verified, helpful_count, created_at, updated_at
        expect($columns)->toHaveCount(10);

        // Check both FKs
        $userIdColumn = $columns->firstWhere('name', 'user_id');
        $productIdColumn = $columns->firstWhere('name', 'product_id');

        expect($userIdColumn)->not->toBeNull()
            ->and((bool) $userIdColumn->is_foreign)->toBeTrue()
            ->and($productIdColumn)->not->toBeNull()
            ->and((bool) $productIdColumn->is_foreign)->toBeTrue();

        // Check relationships created
        $relationships = Relationship::whereHas('toTable', function ($query) {
            $query->where('name', 'reviews');
        })->get();

        expect($relationships)->toHaveCount(2); // user_id, product_id
    });

    it('can parse complex FK with cascadeOnDelete and enum types', function () {
        $usersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_users_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
        );

        $addressesFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_addresses_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000011_create_addresses_table.php')
        );

        /** @disregard */
        $this->parser->exec([$usersFile, $addressesFile], $this->diagram);

        /** @disregard */
        $addressesTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'addresses')
            ->first();

        expect($addressesTable)->not->toBeNull();

        $columns = Column::where('table_id', $addressesTable->id)->get();

        // id, user_id, type, street_address, city, state, postal_code, country, is_default, created_at, updated_at
        expect($columns)->toHaveCount(11);

        // Check FK with index and cascadeOnDelete
        $userIdColumn = $columns->firstWhere('name', 'user_id');
        expect($userIdColumn)->not->toBeNull()
            ->and((bool) $userIdColumn->is_foreign)->toBeTrue();

        // Check enum with 3 values
        $typeColumn = $columns->firstWhere('name', 'type');
        expect($typeColumn)->not->toBeNull()
            ->and($typeColumn->type)->toBe('enum');

        // Check relationship
        $relationship = Relationship::whereHas('toTable', function ($query) {
            $query->where('name', 'addresses');
        })->first();

        expect($relationship)->not->toBeNull();
    });

    it('can parse Schema::table() to add columns to existing table', function () {
        $usersFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_create_users_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
        );

        $addBioFile = UploadedFile::fake()->createWithContent(
            '2024_01_01_add_bio_to_users_table.php',
            File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000012_add_bio_to_users_table.php')
        );

        /** @disregard */
        $this->parser->exec([$usersFile, $addBioFile], $this->diagram);

        /** @disregard */
        $usersTable = Table::where('diagram_id', $this->diagram->id)
            ->where('name', 'users')
            ->first();

        expect($usersTable)->not->toBeNull();

        $columns = Column::where('table_id', $usersTable->id)->get();

        // Original: id, name, email, email_verified_at, password, created_at, updated_at (7)
        // Added: bio, website, birth_date, gender (4)
        expect($columns)->toHaveCount(11);

        // Check added columns
        $bioColumn = $columns->firstWhere('name', 'bio');
        expect($bioColumn)->not->toBeNull()
            ->and($bioColumn->type)->toBe('text');

        $websiteColumn = $columns->firstWhere('name', 'website');
        expect($websiteColumn)->not->toBeNull()
            ->and($websiteColumn->type)->toBe('varchar');

        $birthDateColumn = $columns->firstWhere('name', 'birth_date');
        expect($birthDateColumn)->not->toBeNull()
            ->and($birthDateColumn->type)->toBe('date');

        $genderColumn = $columns->firstWhere('name', 'gender');
        expect($genderColumn)->not->toBeNull()
            ->and($genderColumn->type)->toBe('enum');
    });

    it('can parse complete e-commerce schema', function () {
        $files = [
            UploadedFile::fake()->createWithContent(
                '2024_01_01_000001_create_users_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_01_000006_create_categories_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000006_create_categories_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_01_000007_create_products_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000007_create_products_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_01_000008_create_orders_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000008_create_orders_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_01_000009_create_order_items_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000009_create_order_items_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_01_000010_create_reviews_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000010_create_reviews_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_01_000011_create_addresses_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2024_01_01_000011_create_addresses_table.php')
            ),
        ];

        /** @disregard */
        $this->parser->exec($files, $this->diagram);

        // Check all tables created
        /** @disregard */
        $tables = Table::where('diagram_id', $this->diagram->id)->get();
        expect($tables)->toHaveCount(7);

        // Check all relationships
        $relationships = Relationship::whereIn('from_table_id', $tables->pluck('id'))
            ->orWhereIn('to_table_id', $tables->pluck('id'))
            ->get();

        // Expected relationships:
        // 1. categories -> categories (self-ref)
        // 2. products -> users
        // 3. products -> categories
        // 4. orders -> users (customer_id)
        // 5. order_items -> orders
        // 6. order_items -> products
        // 7. reviews -> users
        // 8. reviews -> products
        // 9. addresses -> users
        expect($relationships)->toHaveCount(9);

        // Verify specific complex scenarios
        $productsTable = $tables->firstWhere('name', 'products');
        $productsColumns = Column::where('table_id', $productsTable->id)->get();

        // Verify UUID primary key
        $productId = $productsColumns->firstWhere('name', 'id');
        expect($productId->type)->toBe('char');

        // Verify JSON column exists
        $metadata = $productsColumns->firstWhere('name', 'metadata');
        expect($metadata->type)->toBe('json');
    });

    it('can parse Seego tenant-style migrations (cache, users, clients)', function () {
        $files = [
            UploadedFile::fake()->createWithContent(
                '0001_01_01_000001_create_cache_table.php',
                File::get(__DIR__.'/Fixtures/migrations/0001_01_01_000001_create_cache_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '0001_01_01_000001_create_users_table.php',
                File::get(__DIR__.'/Fixtures/migrations/0001_01_01_000001_create_users_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2025_01_22_120000_create_clients_table.php',
                File::get(__DIR__.'/Fixtures/migrations/2025_01_22_120000_create_clients_table.php')
            ),
        ];

        /** @disregard */
        $this->parser->exec($files, $this->diagram);

        /** @disregard */
        $tables = Table::where('diagram_id', $this->diagram->id)->get();

        expect($tables)->toHaveCount(6)
            ->and($tables->pluck('name')->sort()->values()->all())->toEqual(
                collect(['cache', 'cache_locks', 'clients', 'password_reset_tokens', 'sessions', 'users'])
                    ->sort()
                    ->values()
                    ->all()
            );

        $cacheTable = $tables->firstWhere('name', 'cache');
        expect($cacheTable)->not->toBeNull();
        $cacheColumns = Column::where('table_id', $cacheTable->id)->get();
        expect($cacheColumns)->toHaveCount(3);
        expect($cacheColumns->pluck('name')->all())->toEqualCanonicalizing(['key', 'value', 'expiration']);

        $usersTable = $tables->firstWhere('name', 'users');
        expect($usersTable)->not->toBeNull();
        $usersColumns = Column::where('table_id', $usersTable->id)->get();
        expect($usersColumns)->toHaveCount(16);

        $clientsTable = $tables->firstWhere('name', 'clients');
        expect($clientsTable)->not->toBeNull();
        $clientsColumns = Column::where('table_id', $clientsTable->id)->get();
        expect($clientsColumns)->toHaveCount(6);
        expect($clientsColumns->firstWhere('name', 'deleted_at'))->not->toBeNull()
            ->and($clientsColumns->firstWhere('name', 'deleted_at')->type)->toBe('timestamp');

        // FKs to organizations* are skipped (referenced tables not in import); sessions.user_id has no constrained()
        $relationships = Relationship::whereIn('from_table_id', $tables->pluck('id'))
            ->orWhereIn('to_table_id', $tables->pluck('id'))
            ->get();
        expect($relationships)->toHaveCount(0);
    });
});

function createMigrationFile(string $tableName, string $blueprint): UploadedFile
{
    $content = <<<PHP
        <?php
        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            public function up(): void
            {
                Schema::create('{$tableName}', function (Blueprint \$table) {
                    {$blueprint}
                });
            }
        };
    PHP;

    return UploadedFile::fake()->createWithContent(
        "create_{$tableName}_table.php",
        $content
    );
}
