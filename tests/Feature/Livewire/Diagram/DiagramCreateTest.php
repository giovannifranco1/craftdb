<?php

namespace Tests\Feature\Livewire\Diagram;

use App\Actions\Diagram\CreateNewDiagram;
use App\Livewire\Diagram\DiagramCreate;
use App\Models\Diagram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->withoutTwoFactor()->create();

    // Cria o team através do relacionamento ownedTeams
    // Isso garante que o sistema configure tudo corretamente
    $this->team = $this->user->ownedTeams()->create([
        'user_id' => $this->user->id,
        'name' => $this->user->name . "'s Team",
    ]);

    // Define o time atual na sessão
    session(['current_team' => $this->team]);

    $this->actingAs($this->user);
});

test('component renders successfully', function () {
    Livewire::test(DiagramCreate::class)
        ->assertStatus(200);
});

test('component initializes with correct default values', function () {
    Livewire::test(DiagramCreate::class)
        ->assertSet('step', 1)
        ->assertSet('name', '')
        ->assertSet('description', '')
        ->assertSet('style', null)
        ->assertSet('database', null)
        ->assertSet('importForm.hasImport', false)
        ->assertSet('importForm.type', null)
        ->assertSet('importForm.file', []);
});

test('can navigate to next step with valid data', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'colorful')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertHasNoErrors();
});

test('cannot navigate to next step without name', function () {
    Livewire::test(DiagramCreate::class)
        ->set('style', 'colorful')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['name' => 'required']);
});

test('cannot navigate to next step without style', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['style' => 'required']);
});

test('cannot navigate to next step with invalid style', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'invalid-style')
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['style']);
});

test('can navigate back to previous step', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'colorful')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->call('prevStep')
        ->assertSet('step', 1);
});

test('previous step does not go below step 1', function () {
    Livewire::test(DiagramCreate::class)
        ->assertSet('step', 1)
        ->call('prevStep')
        ->assertSet('step', 1);
});

test('name field is required', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', '')
        ->set('description', 'Test description')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasErrors(['name' => 'required']);
});

test('name field cannot exceed 255 characters', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', str_repeat('a', 256))
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasErrors(['name' => 'max']);
});

test('description is optional', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('description', null)
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors(['description']);
});

test('description cannot exceed 255 characters', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('description', str_repeat('a', 256))
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasErrors(['description' => 'max']);
});

test('style field is required', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', null)
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasErrors(['style' => 'required']);
});

test('style field must be colorful or neutral', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'invalid')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasErrors(['style' => 'in']);
});

test('database field is required', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'colorful')
        ->set('database', null)
        ->call('create')
        ->assertHasErrors(['database' => 'required']);
});

test('database field must be valid type', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'colorful')
        ->set('database', 'oracle')
        ->call('create')
        ->assertHasErrors(['database' => 'in']);
});

test('can create diagram with mysql database', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('description', 'Test description')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors();

    expect(Diagram::where('name', 'Test Diagram')->exists())->toBeTrue();

    $diagram = Diagram::where('name', 'Test Diagram')->first();
    expect($diagram->description)->toBe('Test description');
    expect($diagram->style)->toBe('colorful');
    expect($diagram->database)->toBe('mysql');
    expect($diagram->team_id)->toBe($this->team->id);
});

test('can create diagram with postgresql database', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'PostgreSQL Diagram')
        ->set('style', 'neutral')
        ->set('database', 'postgresql')
        ->call('create')
        ->assertHasNoErrors();

    $diagram = Diagram::where('name', 'PostgreSQL Diagram')->first();
    expect($diagram->database)->toBe('postgresql');
});

test('can create diagram with sqlite database', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'SQLite Diagram')
        ->set('style', 'neutral')
        ->set('database', 'sqlite')
        ->call('create')
        ->assertHasNoErrors();

    $diagram = Diagram::where('name', 'SQLite Diagram')->first();
    expect($diagram->database)->toBe('sqlite');
});

test('can create diagram with neutral style', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Neutral Diagram')
        ->set('style', 'neutral')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors();

    $diagram = Diagram::where('name', 'Neutral Diagram')->first();
    expect($diagram->style)->toBe('neutral');
});

test('diagram slug is generated automatically', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'My Test Diagram')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors();

    $diagram = Diagram::where('name', 'My Test Diagram')->first();
    expect($diagram->slug)->toContain('my-test-diagram');
    expect(strlen($diagram->slug))->toBeGreaterThan(strlen('my-test-diagram'));
});

test('diagram is associated with current team', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Team Diagram')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors();

    $diagram = Diagram::where('name', 'Team Diagram')->first();
    expect($diagram->team_id)->toBe($this->team->id);
});

test('redirects to diagram page after creation', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertRedirect();
});

test('sets success message in session after creation', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create');

    expect(session('message'))->toBe(__("Diagram created successfully."));
});

test('increments diagrams count in session after creation', function () {
    session(['diagrams_count' => 5]);

    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create');

    expect(session('diagrams_count'))->toBe(6);
});

test('can reset UI state', function () {
    Livewire::test(DiagramCreate::class)
        ->set('step', 2)
        ->set('name', 'Test Name')
        ->set('description', 'Test Description')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->set('importForm.hasImport', true)
        ->call('resetUIState')
        ->assertSet('step', 1)
        ->assertSet('name', '')
        ->assertSet('description', '')
        ->assertSet('style', null)
        ->assertSet('database', null)
        ->assertSet('importForm.hasImport', false)
        ->assertSet('importForm.type', null)
        ->assertSet('importForm.file', []);
});

test('cancel create resets UI state', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Name')
        ->set('style', 'colorful')
        ->call('cancelCreate')
        ->assertSet('step', 1)
        ->assertSet('name', '')
        ->assertSet('style', null);
});

test('updated import form type clears file', function () {
    Livewire::test(DiagramCreate::class)
        ->set('importForm.file', [UploadedFile::fake()->create('test.sql')])
        ->set('importForm.type', 'sql')
        ->assertSet('importForm.file', []);
});

test('can cancel import', function () {
    Livewire::test(DiagramCreate::class)
        ->set('importForm.hasImport', true)
        ->set('importForm.type', 'sql')
        ->call('cancelImport')
        ->assertSet('importForm.hasImport', false);
});

test('can confirm import', function () {
    Livewire::test(DiagramCreate::class)
        ->set('importForm.type', 'sql')
        ->set('importForm.file', [UploadedFile::fake()->create('test.sql', 100, 'text/plain')])
        ->call('confirmImport')
        ->assertSet('importForm.hasImport', true);
});

test('can cancel file import', function () {
    Livewire::test(DiagramCreate::class)
        ->set('importForm.file', [UploadedFile::fake()->create('test.sql')])
        ->call('cancelFileImport');

    // O método cancelFile() limpa o array de files
    Livewire::test(DiagramCreate::class)
        ->assertSet('importForm.file', []);
});

test('create action is called with correct parameters', function () {
    $actionMock = \Mockery::mock(CreateNewDiagram::class);
    $actionMock->shouldReceive('exec')
        ->once()
        ->with(\Mockery::on(function ($data) {
            return $data['name'] === 'Test Diagram'
                && $data['description'] === 'Test description'
                && $data['style'] === 'colorful'
                && $data['database'] === 'mysql';
        }))
        ->andReturn(Diagram::factory()->make(['id' => 1, 'slug' => 'test-diagram-abc123']));

    $this->app->instance(CreateNewDiagram::class, $actionMock);

    Livewire::test(DiagramCreate::class)
        ->set('name', 'Test Diagram')
        ->set('description', 'Test description')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create');
});

test('creates diagram without description', function () {
    Livewire::test(DiagramCreate::class)
        ->set('name', 'No Description Diagram')
        ->set('description', '')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors();

    $diagram = Diagram::where('name', 'No Description Diagram')->first();
    expect($diagram->description)->toBeEmpty();
});

test('mount initializes component correctly', function () {
    $component = Livewire::test(DiagramCreate::class);

    $component->assertSet('step', 1)
        ->assertSet('name', '')
        ->assertSet('description', '')
        ->assertSet('style', null)
        ->assertSet('database', null);
});

test('multiple users can create diagrams in different teams', function () {
    // Criar segundo usuário com seu time
    $user2 = User::factory()->withoutTwoFactor()->create();
    $team2 = $user2->ownedTeams()->create([
        'user_id' => $user2->id,
        'name' => $user2->name . "'s Team",
    ]);

    // Primeiro usuário cria diagrama
    session(['current_team' => $this->team]);
    $this->actingAs($this->user);

    Livewire::test(DiagramCreate::class)
        ->set('name', 'User 1 Diagram')
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create');

    // Segundo usuário cria diagrama
    session(['current_team' => $team2]);
    $this->actingAs($user2);

    Livewire::test(DiagramCreate::class)
        ->set('name', 'User 2 Diagram')
        ->set('style', 'neutral')
        ->set('database', 'postgresql')
        ->call('create');

    $diagram1 = Diagram::where('name', 'User 1 Diagram')->first();
    $diagram2 = Diagram::where('name', 'User 2 Diagram')->first();

    expect($diagram1->team_id)->toBe($this->team->id);
    expect($diagram2->team_id)->toBe($team2->id);
    expect($diagram1->team_id)->not->toBe($diagram2->team_id);
});

test('long diagram names are handled correctly', function () {
    $longName = str_repeat('A', 255);

    Livewire::test(DiagramCreate::class)
        ->set('name', $longName)
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors(['name']);

    expect(Diagram::where('name', $longName)->exists())->toBeTrue();
});

test('special characters in name are handled correctly', function () {
    $specialName = "Test Diagram: With & Special @Characters #123";

    Livewire::test(DiagramCreate::class)
        ->set('name', $specialName)
        ->set('style', 'colorful')
        ->set('database', 'mysql')
        ->call('create')
        ->assertHasNoErrors();

    expect(Diagram::where('name', $specialName)->exists())->toBeTrue();
});
