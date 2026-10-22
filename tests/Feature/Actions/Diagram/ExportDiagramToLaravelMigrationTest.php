<?php

namespace Tests\Feature\Actions\Diagram;

use App\Actions\Diagram\ExportDiagramToLaravelMigration;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Relationship;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var TestCase $this */
    $this->user = User::factory()->create();

    $this->team = Team::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->diagram = Diagram::factory()->create([
        'name' => 'Test Diagram',
        'database' => 'mysql',
        'team_id' => $this->team->id,
    ]);

    $this->action = new ExportDiagramToLaravelMigration;
});

describe('ExportDiagramToLaravelMigration generation', function (): void {

    beforeEach(function (): void {
        /** @var TestCase $this */
        $this->actingAs($this->user);
    });

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    function createTable(object $test, string $name): Table
    {
        return Table::factory()->create([
            'diagram_id' => $test->diagram->id,
            'name' => $name,
        ]);
    }

    function createColumn(Table $table, array $attrs): Column
    {
        return Column::factory()->create(array_merge(['table_id' => $table->id], $attrs));
    }

    function contentFor(array $files, string $tableName): ?string
    {
        foreach ($files as $file) {
            if (str_contains($file['filename'], "create_{$tableName}_table")) {
                return $file['content'];
            }
        }

        return null;
    }

    // ---------------------------------------------------------------------------
    // Special Methods Detection
    // ---------------------------------------------------------------------------

    describe('special methods detection', function (): void {

        it('detects $table->id() shorthand', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'users');
            createColumn($table, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'name', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 1, 'length' => 255, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'users');

            expect($content)->toContain('$table->id();')
                ->and($content)->not->toContain("bigInteger('id')");
        });

        it('detects $table->timestamps() shorthand', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'posts');
            createColumn($table, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'created_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'updated_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 2, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'posts');

            expect($content)->toContain('$table->timestamps();')
                ->and($content)->not->toContain("timestamp('created_at')")
                ->and($content)->not->toContain("timestamp('updated_at')");
        });

        it('detects $table->softDeletes() shorthand', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'articles');
            createColumn($table, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'deleted_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'articles');

            expect($content)->toContain('$table->softDeletes();')
                ->and($content)->not->toContain("timestamp('deleted_at')");
        });

        it('detects $table->rememberToken() shorthand', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'users');
            createColumn($table, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'remember_token', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 1, 'length' => 100, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'users');

            expect($content)->toContain('$table->rememberToken();')
                ->and($content)->not->toContain("string('remember_token')");
        });

        it('does NOT detect timestamps() when only created_at exists', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'logs');
            createColumn($table, ['name' => 'created_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'logs');

            expect($content)->not->toContain('$table->timestamps();')
                ->and($content)->toContain("timestamp('created_at')");
        });

        it('does NOT detect id() when column is not primary', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'refs');
            createColumn($table, ['name' => 'id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'refs');

            expect($content)->not->toContain('$table->id();')
                ->and($content)->toContain("bigInteger('id')");
        });
    });

    // ---------------------------------------------------------------------------
    // Type Mapping (data provider via with())
    // ---------------------------------------------------------------------------

    describe('type mapping', function (): void {

        it('maps database types to correct Laravel methods', function (string $dbType, string $expectedMethod) {
            /** @var TestCase $this */
            $table = createTable($this, 'test_types');
            createColumn($table, [
                'name' => 'col',
                'type' => $dbType,
                'is_primary' => false,
                'is_foreign' => false,
                'is_notnull' => true,
                'order' => 0,
                'length' => null,
                'default_value' => null,
                'comment' => null,
            ]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'test_types');

            expect($content)->toContain("\$table->{$expectedMethod}('col')");
        })->with([
            'bigint → bigInteger' => ['bigint', 'bigInteger'],
            'int → integer' => ['int', 'integer'],
            'mediumint → mediumInteger' => ['mediumint', 'mediumInteger'],
            'smallint → smallInteger' => ['smallint', 'smallInteger'],
            'tinyint → tinyInteger' => ['tinyint', 'tinyInteger'],
            'text → text' => ['text', 'text'],
            'date → date' => ['date', 'date'],
            'datetime → dateTime' => ['datetime', 'dateTime'],
            'timestamp → timestamp' => ['timestamp', 'timestamp'],
            'time → time' => ['time', 'time'],
            'year → year' => ['year', 'year'],
            'decimal → decimal' => ['decimal', 'decimal'],
            'float → float' => ['float', 'float'],
            'double → double' => ['double', 'double'],
            'boolean → boolean' => ['boolean', 'boolean'],
            'json → json' => ['json', 'json'],
        ]);

        it('falls back to string for unknown types', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'unknowns');
            createColumn($table, [
                'name' => 'mystery',
                'type' => 'unknowntype',
                'is_primary' => false,
                'is_foreign' => false,
                'is_notnull' => true,
                'order' => 0,
                'length' => null,
                'default_value' => null,
                'comment' => null,
            ]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'unknowns');

            expect($content)->toContain("\$table->string('mystery')");
        });
    });

    // ---------------------------------------------------------------------------
    // Column length handling
    // ---------------------------------------------------------------------------

    describe('column length', function (): void {

        it('omits default varchar length 255', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'names');
            createColumn($table, ['name' => 'title', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => 255, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'names');

            expect($content)->toContain("\$table->string('title')")
                ->and($content)->not->toContain("'title', 255");
        });

        it('includes non-default varchar length', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'codes');
            createColumn($table, ['name' => 'code', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => 50, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'codes');

            expect($content)->toContain("\$table->string('code', 50)");
        });

        it('omits default char length 36', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'uuids');
            createColumn($table, ['name' => 'uuid', 'type' => 'char', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => 36, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'uuids');

            expect($content)->toContain("\$table->char('uuid')")
                ->and($content)->not->toContain("'uuid', 36");
        });
    });

    // ---------------------------------------------------------------------------
    // Column modifiers
    // ---------------------------------------------------------------------------

    describe('column modifiers', function (): void {

        it('adds ->nullable() for nullable columns', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'profiles');
            createColumn($table, ['name' => 'bio', 'type' => 'text', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'profiles');

            expect($content)->toContain("\$table->text('bio')->nullable()");
        });

        it('adds numeric default value', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'settings');
            createColumn($table, ['name' => 'retries', 'type' => 'int', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => '3', 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'settings');

            expect($content)->toContain('->default(3)');
        });

        it('adds boolean default value', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'flags');
            createColumn($table, ['name' => 'active', 'type' => 'tinyint', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => 'true', 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'flags');

            expect($content)->toContain('->default(true)');
        });

        it('adds NULL default value', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'optionals');
            createColumn($table, ['name' => 'note', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 0, 'length' => 255, 'default_value' => 'NULL', 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'optionals');

            expect($content)->toContain('->default(null)');
        });

        it('adds string default value', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'configs');
            createColumn($table, ['name' => 'locale', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => 255, 'default_value' => 'en', 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'configs');

            expect($content)->toContain("->default('en')");
        });

        it('adds comment modifier', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'annotated');
            createColumn($table, ['name' => 'score', 'type' => 'int', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => 'User score']);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'annotated');

            expect($content)->toContain("->comment('User score')");
        });

        it('combines nullable, default, and comment modifiers', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'combined');
            createColumn($table, ['name' => 'status', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 0, 'length' => 255, 'default_value' => 'draft', 'comment' => 'Current status']);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'combined');

            expect($content)->toContain('->nullable()')
                ->and($content)->toContain("->default('draft')")
                ->and($content)->toContain("->comment('Current status')");
        });
    });

    // ---------------------------------------------------------------------------
    // Foreign Keys
    // ---------------------------------------------------------------------------

    describe('foreign key generation', function (): void {

        it('generates foreignId()->constrained() for FK with inferred table', function () {
            /** @var TestCase $this */
            $usersTable = createTable($this, 'users');
            $usersId = createColumn($usersTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $postsTable = createTable($this, 'posts');
            createColumn($postsTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $userIdCol = createColumn($postsTable, ['name' => 'user_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent(users.id), to=child(posts.user_id)
            Relationship::factory()->between($usersTable, $usersId, $postsTable, $userIdCol)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'posts');

            expect($content)->toContain("\$table->foreignId('user_id')->constrained('users');");
        });

        it('generates foreignId()->constrained(table) for FK with explicit table', function () {
            /** @var TestCase $this */
            $authorsTable = createTable($this, 'authors');
            $authorsId = createColumn($authorsTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $booksTable = createTable($this, 'books');
            createColumn($booksTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $writerIdCol = createColumn($booksTable, ['name' => 'writer_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent(authors.id), to=child(books.writer_id)
            Relationship::factory()->between($authorsTable, $authorsId, $booksTable, $writerIdCol)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'books');

            // writer_id would infer "writers" table, but the real table is "authors"
            expect($content)->toContain("\$table->foreignId('writer_id')->constrained('authors');");
        });

        it('generates nullable foreignId', function () {
            /** @var TestCase $this */
            $categoriesTable = createTable($this, 'categories');
            $catId = createColumn($categoriesTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $productsTable = createTable($this, 'products');
            createColumn($productsTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $catIdCol = createColumn($productsTable, ['name' => 'category_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => false, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent(categories.id), to=child(products.category_id)
            Relationship::factory()->between($categoriesTable, $catId, $productsTable, $catIdCol)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'products');

            expect($content)->toContain("\$table->foreignId('category_id')->nullable()->constrained('categories');");
        });

        it('generates unsignedBigInteger for FK without relationship', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'orphans');
            createColumn($table, ['name' => 'legacy_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'orphans');

            expect($content)->toContain("\$table->unsignedBigInteger('legacy_id');")
                ->and($content)->not->toContain('foreignId')
                ->and($content)->not->toContain('constrained');
        });
    });

    // ---------------------------------------------------------------------------
    // Migration file structure
    // ---------------------------------------------------------------------------

    describe('migration file structure', function (): void {

        it('generates valid PHP migration file structure', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'users');
            createColumn($table, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = $files[0]['content'];

            expect($content)
                ->toContain('use Illuminate\Database\Migrations\Migration;')
                ->toContain('use Illuminate\Database\Schema\Blueprint;')
                ->toContain('use Illuminate\Support\Facades\Schema;')
                ->toContain('return new class extends Migration')
                ->toContain("Schema::create('users', function (Blueprint \$table)")
                ->toContain("Schema::dropIfExists('users')");
        });

        it('generates sequential timestamps in filenames', function () {
            /** @var TestCase $this */
            createTable($this, 'alpha');
            createTable($this, 'beta');
            createTable($this, 'gamma');

            $files = $this->action->exec($this->diagram->fresh());
            $today = now()->format('Y_m_d');

            expect($files)->toHaveCount(3);

            $filenames = array_column($files, 'filename');

            // All start with today's date
            foreach ($filenames as $fn) {
                expect($fn)->toStartWith($today);
            }

            // Sequential numbering
            $sequences = array_map(function ($fn) use ($today) {
                preg_match("/{$today}_(\d{6})_create_/", $fn, $m);

                return (int) ($m[1] ?? 0);
            }, $filenames);

            expect($sequences)->toEqual([1, 2, 3]);
        });

        it('returns one file per table', function () {
            /** @var TestCase $this */
            createTable($this, 'table_a');
            createTable($this, 'table_b');

            $files = $this->action->exec($this->diagram->fresh());
            $filenames = implode('|', array_column($files, 'filename'));

            expect($files)->toHaveCount(2)
                ->and($filenames)->toContain('create_table_a_table')
                ->and($filenames)->toContain('create_table_b_table');
        });

        it('returns empty array for diagram with no tables', function () {
            /** @var TestCase $this */
            $files = $this->action->exec($this->diagram);

            expect($files)->toBeArray()->toBeEmpty();
        });
    });

    // ---------------------------------------------------------------------------
    // Topological sort / dependency ordering
    // ---------------------------------------------------------------------------

    describe('topological sort', function (): void {

        it('places referenced table before the referencing table', function () {
            /** @var TestCase $this */
            $usersTable = createTable($this, 'users');
            $usersId = createColumn($usersTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $postsTable = createTable($this, 'posts');
            createColumn($postsTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $fkCol = createColumn($postsTable, ['name' => 'user_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent(users.id), to=child(posts.user_id)
            Relationship::factory()->between($usersTable, $usersId, $postsTable, $fkCol)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());
            $filenames = array_column($files, 'filename');

            $usersIndex = null;
            $postsIndex = null;
            foreach ($filenames as $i => $fn) {
                if (str_contains($fn, 'create_users_table')) {
                    $usersIndex = $i;
                }
                if (str_contains($fn, 'create_posts_table')) {
                    $postsIndex = $i;
                }
            }

            expect($usersIndex)->toBeLessThan($postsIndex);
        });

        it('handles three-level dependency chain', function () {
            /** @var TestCase $this */
            $countriesTable = createTable($this, 'countries');
            $countriesId = createColumn($countriesTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $citiesTable = createTable($this, 'cities');
            $citiesId = createColumn($citiesTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $cityCountryFk = createColumn($citiesTable, ['name' => 'country_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            $addressesTable = createTable($this, 'addresses');
            createColumn($addressesTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $addrCityFk = createColumn($addressesTable, ['name' => 'city_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent, to=child
            Relationship::factory()->between($countriesTable, $countriesId, $citiesTable, $cityCountryFk)->oneToMany()->create();
            Relationship::factory()->between($citiesTable, $citiesId, $addressesTable, $addrCityFk)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());
            $filenames = array_column($files, 'filename');

            $indexOf = fn (string $needle) => collect($filenames)->search(fn ($fn) => str_contains($fn, $needle));

            expect($indexOf('countries'))->toBeLessThan($indexOf('cities'))
                ->and($indexOf('cities'))->toBeLessThan($indexOf('addresses'));
        });

        it('handles circular dependencies without infinite loop', function () {
            /** @var TestCase $this */
            $tableA = createTable($this, 'table_a');
            $colAId = createColumn($tableA, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $colAFk = createColumn($tableA, ['name' => 'table_b_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            $tableB = createTable($this, 'table_b');
            $colBId = createColumn($tableB, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $colBFk = createColumn($tableB, ['name' => 'table_a_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent, to=child (circular)
            Relationship::factory()->between($tableB, $colBId, $tableA, $colAFk)->oneToMany()->create();
            Relationship::factory()->between($tableA, $colAId, $tableB, $colBFk)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());

            // Should not hang and should produce both files
            expect($files)->toHaveCount(2);
        });

        it('tables without FK come first', function () {
            /** @var TestCase $this */
            $dependentTable = createTable($this, 'orders');
            createColumn($dependentTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            $fk = createColumn($dependentTable, ['name' => 'user_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 1, 'length' => null, 'default_value' => null, 'comment' => null]);

            $independentTable = createTable($this, 'users');
            $uId = createColumn($independentTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent(users.id), to=child(orders.user_id)
            Relationship::factory()->between($independentTable, $uId, $dependentTable, $fk)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());

            expect($files[0]['filename'])->toContain('create_users_table')
                ->and($files[1]['filename'])->toContain('create_orders_table');
        });
    });

    // ---------------------------------------------------------------------------
    // Full table scenario (complete Users table)
    // ---------------------------------------------------------------------------

    describe('complete table scenario', function (): void {

        it('generates a complete users table migration with all features', function () {
            /** @var TestCase $this */
            $table = createTable($this, 'users');

            createColumn($table, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'name', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 1, 'length' => 255, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'email', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 2, 'length' => 255, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'password', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 3, 'length' => 255, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'remember_token', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 4, 'length' => 100, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'created_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 5, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($table, ['name' => 'updated_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 6, 'length' => null, 'default_value' => null, 'comment' => null]);

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'users');

            expect($content)
                ->toContain('$table->id();')
                ->toContain("\$table->string('name')")
                ->toContain("\$table->string('email')")
                ->toContain("\$table->string('password')")
                ->toContain('$table->rememberToken();')
                ->toContain('$table->timestamps();')
                ->not->toContain("bigInteger('id')")
                ->not->toContain("timestamp('created_at')")
                ->not->toContain("timestamp('updated_at')")
                ->not->toContain("string('remember_token')");
        });

        it('generates a table with FK, softDeletes, and mixed modifiers', function () {
            /** @var TestCase $this */
            $usersTable = createTable($this, 'users');
            $usersId = createColumn($usersTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);

            $postsTable = createTable($this, 'posts');
            createColumn($postsTable, ['name' => 'id', 'type' => 'bigint', 'is_primary' => true, 'is_foreign' => false, 'is_notnull' => true, 'order' => 0, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($postsTable, ['name' => 'title', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 1, 'length' => 255, 'default_value' => null, 'comment' => null]);
            $fkCol = createColumn($postsTable, ['name' => 'user_id', 'type' => 'bigint', 'is_primary' => false, 'is_foreign' => true, 'is_notnull' => true, 'order' => 2, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($postsTable, ['name' => 'status', 'type' => 'varchar', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 3, 'length' => 20, 'default_value' => 'draft', 'comment' => 'Post status']);
            createColumn($postsTable, ['name' => 'views', 'type' => 'int', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => true, 'order' => 4, 'length' => null, 'default_value' => '0', 'comment' => null]);
            createColumn($postsTable, ['name' => 'deleted_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 5, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($postsTable, ['name' => 'created_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 6, 'length' => null, 'default_value' => null, 'comment' => null]);
            createColumn($postsTable, ['name' => 'updated_at', 'type' => 'timestamp', 'is_primary' => false, 'is_foreign' => false, 'is_notnull' => false, 'order' => 7, 'length' => null, 'default_value' => null, 'comment' => null]);

            // from=parent(users.id), to=child(posts.user_id)
            Relationship::factory()->between($usersTable, $usersId, $postsTable, $fkCol)->oneToMany()->create();

            $files = $this->action->exec($this->diagram->fresh());
            $content = contentFor($files, 'posts');

            expect($content)
                ->toContain('$table->id();')
                ->toContain("\$table->string('title')")
                ->toContain("\$table->foreignId('user_id')->constrained('users');")
                ->toContain("\$table->string('status', 20)->default('draft')->comment('Post status');")
                ->toContain("\$table->integer('views')->default(0);")
                ->toContain('$table->softDeletes();')
                ->toContain('$table->timestamps();');
        });
    });

});

// ---------------------------------------------------------------------------
// Export endpoint
// ---------------------------------------------------------------------------

describe('ExportLaravelMigration endpoint', function (): void {

    it('returns 200 with zip content-type for the local user', function () {
        /** @var TestCase $this */
        createTable($this, 'users');

        $this->actingAs($this->user)
            ->get(route('diagram.export-laravel-migration', [
                'team_id' => $this->team->id,
                'diagram' => $this->diagram->slug,
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/zip');
    });

    it('returns 403 for user without team access', function () {
        /** @var TestCase $this */
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('diagram.export-laravel-migration', [
                'team_id' => $this->team->id,
                'diagram' => $this->diagram->slug,
            ]))
            ->assertForbidden();
    });

    it('does not export for the implicit local user outside the team', function () {
        /** @var TestCase $this */
        $response = $this->get(route('diagram.export-laravel-migration', [
            'team_id' => $this->team->id,
            'diagram' => $this->diagram->slug,
        ]));

        $response->assertForbidden();
    });

    it('downloaded filename contains migrations and date', function () {
        /** @var TestCase $this */
        createTable($this, 'users');

        $response = $this->actingAs($this->user)
            ->get(route('diagram.export-laravel-migration', [
                'team_id' => $this->team->id,
                'diagram' => $this->diagram->slug,
            ]));

        $response->assertOk();
        $disposition = $response->headers->get('Content-Disposition');
        expect($disposition)->toContain('-migrations-')
            ->and($disposition)->toContain('.zip');
    });
});
