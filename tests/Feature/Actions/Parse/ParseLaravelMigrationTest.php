<?php
namespace Tests\Feature\Actions\Parse;

use App\Actions\Diagram\AddRelationshipWithExistingForeignKey;
use App\Actions\Parse\ParseLaravelMigration;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Relationship;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use ReflectionClass;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @disregard */
    $this->diagram = Diagram::factory()->create([
        'name' => 'Test Diagram',
        'style' => 'neutral',
        'database' => 'mysql',
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
                File::get(__DIR__ . '/Fixtures/migrations/2024_01_01_000001_create_users_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_02_create_posts_table.php',
                File::get(__DIR__ . '/Fixtures/migrations/2024_01_01_000002_create_posts_table.php')
            ),
            UploadedFile::fake()->createWithContent(
                '2024_01_03_create_comments_table.php',
                File::get(__DIR__ . '/Fixtures/migrations/2024_01_01_000003_create_comments_table.php')
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

