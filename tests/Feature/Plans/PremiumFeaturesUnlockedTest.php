<?php

use App\Actions\Diagram\ImportToExistingDiagram;
use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->autenticated();
    Livewire::withoutLazyLoading();
    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id, 'database' => 'mysql']);
});

test('FREE06 export json works for the local user', function () {
    $this->get(route('diagram.export-json', ['team_id' => $this->team->id, 'diagram' => $this->diagram->slug]))
        ->assertOk();
});

test('FREE06 export laravel migration works for the local user', function () {
    $this->get(route('diagram.export-laravel-migration', ['team_id' => $this->team->id, 'diagram' => $this->diagram->slug]))
        ->assertOk();
});

test('FREE07 areas can be created for the local user', function () {
    Livewire::test('diagram.canvas.lists.⚡area-list', ['diagram' => $this->diagram])
        ->call('addArea', 10, 10)
        ->assertDispatched('area::added');

    expect($this->diagram->areas()->count())->toBe(1);
});

test('FREE07 notes can be created for the local user', function () {
    Livewire::test('diagram.canvas.lists.⚡note-list', ['diagram' => $this->diagram])
        ->call('addNote', 10, 10)
        ->assertDispatched('note::added');

    expect($this->diagram->notes()->count())->toBe(1);
});

test('FREE07 graph loads for the local user', function () {
    $component = Livewire::test('diagram.ui.⚡graph', ['diagram' => $this->diagram])->assertOk();

    expect($component->get('loadGraph'))->toBeTrue();
});

test('FREE05 import to existing diagram works for the local user', function () {
    $sql = 'CREATE TABLE `users` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, PRIMARY KEY (`id`));'
        .'CREATE TABLE `posts` (`id` bigint unsigned NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, PRIMARY KEY (`id`));';
    $file = UploadedFile::fake()->createWithContent('schema.sql', $sql);

    app(ImportToExistingDiagram::class)->exec($this->diagram, [$file], 'sql');

    expect($this->diagram->tables()->count())->toBe(2);
});

test('FREE08 any user can create a workspace', function () {
    $this->get(route('workspaces.index'))->assertOk();

    Livewire::test('settings.⚡workspaces')
        ->set('createName', 'Another team')
        ->call('create')
        ->assertHasNoErrors();

    expect(Team::where('name', 'Another team')->exists())->toBeTrue();
});
