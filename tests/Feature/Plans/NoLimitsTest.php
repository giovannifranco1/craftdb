<?php

use App\Actions\Diagram\AddColumnToTable;
use App\Actions\Diagram\CreateNewDiagram;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->autenticated();
});

test('FREE01 creates 5 diagrams for the local user', function () {
    foreach (range(1, 5) as $i) {
        app(CreateNewDiagram::class)->exec([
            'name' => "Diagram $i",
            'style' => 'neutral',
            'database' => 'mysql',
        ]);
    }

    expect($this->team->diagrams()->count())->toBe(5);
});

test('FREE01 the create component does not block creating 5 diagrams', function () {
    foreach (range(1, 5) as $i) {
        Livewire::test('diagram.⚡create')
            ->set('name', "Diagram $i")
            ->set('style', 'neutral')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasNoErrors();
    }

    expect($this->team->diagrams()->count())->toBe(5);
});

test('FREE02 imports 120 tables from a SQL file', function () {
    $sql = '';
    foreach (range(1, 120) as $i) {
        $sql .= "CREATE TABLE `table_$i` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, PRIMARY KEY (`id`));\n";
    }

    $diagram = app(CreateNewDiagram::class)->exec([
        'name' => 'Big schema',
        'style' => 'neutral',
        'database' => 'mysql',
        'type' => 'sql',
        'file' => [UploadedFile::fake()->createWithContent('schema.sql', $sql)],
    ]);

    expect($diagram->tables()->count())->toBe(120);
});

test('FREE02 adds 120 columns to the same table', function () {
    $diagram = app(CreateNewDiagram::class)->exec([
        'name' => 'Wide',
        'style' => 'neutral',
        'database' => 'mysql',
    ]);
    $table = Table::factory()->create(['diagram_id' => $diagram->id, 'resize_column_position' => 5]);

    foreach (range(1, 120) as $i) {
        app(AddColumnToTable::class)->exec($table);
    }

    expect($table->columns()->count())->toBeGreaterThanOrEqual(120);
});

test('FREE04 limit classes no longer exist', function () {
    expect(class_exists('App\Support\DiagramLimits'))->toBeFalse()
        ->and(class_exists('App\Exceptions\DiagramLimitExceededException'))->toBeFalse()
        ->and(class_exists('App\Exceptions\TableLimitExceededException'))->toBeFalse()
        ->and(class_exists('App\Exceptions\TableColumnLimitExceededException'))->toBeFalse();
});
