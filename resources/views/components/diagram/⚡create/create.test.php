<?php

use App\Actions\Diagram\CreateNewDiagram;
use App\Models\Diagram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */

    $this->autenticated();

    $this->component = Livewire::test('diagram.diagram-create');
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->get(route('dashboard'))->assertOk();
        $this->component->assertOk();
    });

    it('initializes with correct default values', function () {
        /** @var Testcase $this */

        $this->component
            ->assertSet('step', 1)
            ->assertSet('name', '')
            ->assertSet('description', '')
            ->assertSet('style', null)
            ->assertSet('database', null)
            ->assertSet('importForm.hasImport', false)
            ->assertSet('importForm.type', null)
            ->assertSet('importForm.file', []);
    });

    it('mount initializes component correctly', function () {
        /** @var Testcase $this */

        $this->component->assertSet('step', 1)
            ->assertSet('name', '')
            ->assertSet('description', '')
            ->assertSet('style', null)
            ->assertSet('database', null);
    });
})->group('livewire-components');

describe('Validation', function() {
    it('validates required fields', function (): void {
        /** @var Testcase $this */

        $this->component
            ->call('create')
            ->assertHasErrors(['name', 'style', 'database']);
    });

    it('name field is required', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', '')
            ->set('description', 'Test description')
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasErrors(['name' => 'required']);
    });

    it('name field cannot exceed 255 characters', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', str_repeat('a', 256))
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasErrors(['name' => 'max']);
    });

    it('description is optional', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('description', null)
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasNoErrors(['description']);
    });

    it('description cannot exceed 255 characters', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('description', str_repeat('a', 256))
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasErrors(['description' => 'max']);
    });

    it('style field is required', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', null)
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasErrors(['style' => 'required']);
    });

    it('style field must be colorful or neutral', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', 'invalid')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasErrors(['style' => 'in']);
    });

    it('database field is required', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', 'colorful')
            ->set('database', null)
            ->call('create')
            ->assertHasErrors(['database' => 'required']);
    });

    it('database field must be valid type', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', 'colorful')
            ->set('database', 'oracle')
            ->call('create')
            ->assertHasErrors(['database' => 'in']);
    });
})->group('livewire-components');

describe('Step Navigation', function() {
    it('navigates through steps', function (): void {
        /** @var TestCase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', 'neutral')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->call('prevStep')
            ->assertSet('step', 1);
    });

    it('can navigate to next step with valid data', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', 'colorful')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertHasNoErrors();
    });

    it('cannot navigate to next step without name', function () {
        /** @var Testcase $this */

        $this->component
            ->set('style', 'colorful')
            ->call('nextStep')
            ->assertSet('step', 1)
            ->assertHasErrors(['name' => 'required']);
    });

    it('cannot navigate to next step without style', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->call('nextStep')
            ->assertSet('step', 1)
            ->assertHasErrors(['style' => 'required']);
    });

    it('cannot navigate to next step with invalid style', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', 'invalid-style')
            ->call('nextStep')
            ->assertSet('step', 1)
            ->assertHasErrors(['style']);
    });

    it('can navigate back to previous step', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('style', 'colorful')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->call('prevStep')
            ->assertSet('step', 1);
    });

    it('previous step does not go below step 1', function () {
        /** @var Testcase $this */

        $this->component
            ->assertSet('step', 1)
            ->call('prevStep')
            ->assertSet('step', 1);
    });
})->group('livewire-components');

describe('Diagram Creation', function() {
    it('can create a new diagram', function (): void {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Diagram')
            ->set('description', 'Test description')
            ->set('style', 'neutral')
            ->set('database', 'mysql')
            ->call('create');

        $this->assertDatabaseHas('diagram', [
            'name' => 'Test Diagram',
            'team_id' => $this->team->id,
            'style' => 'neutral',
            'database' => 'mysql',
        ]);
    });

    it('diagram is associated with current team', function () {
        /** @var TestCase $this */

        $this->component
            ->set('name', 'Team Diagram')
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasNoErrors();

        $diagram = Diagram::where('name', 'Team Diagram')->first();
        expect($diagram->team_id)->toBe($this->team->id);
    });

    it('creates diagram without description', function () {
        /** @var TestCase $this */

        $this->component
            ->set('name', 'No Description Diagram')
            ->set('description', '')
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasNoErrors();

        $diagram = Diagram::where('name', 'No Description Diagram')->first();
        expect($diagram->description)->toBeEmpty();
    });

    it('diagram slug is generated automatically', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'My Test Diagram')
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasNoErrors();

        $diagram = Diagram::where('name', 'My Test Diagram')->first();
        expect($diagram->slug)->toContain('my-test-diagram');
        expect(strlen($diagram->slug))->toBeGreaterThan(strlen('my-test-diagram'));
    });

    describe('Database Types', function() {
        it('can create diagram with mysql database', function () {
            /** @var TestCase $this */

            $this->component
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

        it('can create diagram with postgresql database', function () {
            /** @var Testcase $this */

            $this->component
                ->set('name', 'PostgreSQL Diagram')
                ->set('style', 'neutral')
                ->set('database', 'postgresql')
                ->call('create')
                ->assertHasNoErrors();

            $diagram = Diagram::where('name', 'PostgreSQL Diagram')->first();
            expect($diagram->database)->toBe('postgresql');
        });

        it('can create diagram with sqlite database', function () {
            /** @var Testcase $this */

            $this->component
                ->set('name', 'SQLite Diagram')
                ->set('style', 'neutral')
                ->set('database', 'sqlite')
                ->call('create')
                ->assertHasNoErrors();

            $diagram = Diagram::where('name', 'SQLite Diagram')->first();
            expect($diagram->database)->toBe('sqlite');
        });
    });

    describe('Style Types', function() {
        it('can create diagram with neutral style', function () {
            /** @var Testcase $this */

            $this->component
                ->set('name', 'Neutral Diagram')
                ->set('style', 'neutral')
                ->set('database', 'mysql')
                ->call('create')
                ->assertHasNoErrors();

            $diagram = Diagram::where('name', 'Neutral Diagram')->first();
            expect($diagram->style)->toBe('neutral');
        });
    });

    describe('Post-Creation Behavior', function() {
        it('redirects to diagram page after creation', function () {
            /** @var Testcase $this */

            $this->component
                ->set('name', 'Test Diagram')
                ->set('style', 'colorful')
                ->set('database', 'mysql')
                ->call('create')
                ->assertRedirect();
        });

        it('sets success message in session after creation', function () {
            /** @var Testcase $this */

            $this->component
                ->set('name', 'Test Diagram')
                ->set('style', 'colorful')
                ->set('database', 'mysql')
                ->call('create');

            expect(session('message'))->toBe(__("Diagram created successfully."));
        });

        it('increments diagrams count in session after creation', function () {
            session(['diagrams_count' => 5]);

            /** @var Testcase $this */

            $this->component
                ->set('name', 'Test Diagram')
                ->set('style', 'colorful')
                ->set('database', 'mysql')
                ->call('create');

            expect(session('diagrams_count'))->toBe(6);
        });
    });
})->group('livewire-components');

describe('Import Functionality', function() {
    it('updated import form type clears file', function () {
        /** @var Testcase $this */

        $this->component
            ->set('importForm.file', [UploadedFile::fake()->create('test.sql')])
            ->set('importForm.type', 'sql')
            ->assertSet('importForm.file', []);
    });

    it('can cancel import', function () {
        /** @var Testcase $this */

        $this->component
            ->set('importForm.hasImport', true)
            ->set('importForm.type', 'sql')
            ->call('cancelImport')
            ->assertSet('importForm.hasImport', false);
    });

    it('can confirm import', function () {
        /** @var Testcase $this */

        $this->component
            ->set('importForm.type', 'sql')
            ->set('importForm.file', [UploadedFile::fake()->create('test.sql', 100, 'text/plain')])
            ->call('confirmImport')
            ->assertSet('importForm.hasImport', true);
    });

    it('can cancel file import', function () {
        /** @var Testcase $this */

        $this->component
            ->set('importForm.file', [UploadedFile::fake()->create('test.sql')])
            ->call('cancelFileImport');

        // O método cancelFile() limpa o array de files
        /** @var Testcase $this */

        $this->component
            ->assertSet('importForm.file', []);
    });
})->group('livewire-components');

describe('UI State Management', function() {
    it('can reset UI state', function () {
        /** @var Testcase $this */

        $this->component
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

    it('cancel create resets UI state', function () {
        /** @var Testcase $this */

        $this->component
            ->set('name', 'Test Name')
            ->set('style', 'colorful')
            ->call('cancelCreate')
            ->assertSet('step', 1)
            ->assertSet('name', '')
            ->assertSet('style', null);
    });
});

describe('Integration & Actions', function() {
    it('create action is called with correct parameters', function () {
        /** @var TestCase $this */

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
        $this->component
            ->set('name', 'Test Diagram')
            ->set('description', 'Test description')
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create');
    });
})->group('livewire-components');

describe('Multi-User Scenarios', function() {
    it('multiple users can create diagrams in different teams', function () {
        /** @var TestCase $this */
        $this->component
            ->set('name', 'User 1 Diagram')
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create');

        $user2 = User::factory()->withoutTwoFactor()->create();
        $team2 = $user2->ownedTeams()->create([
            'user_id' => $user2->id,
            'name' => $user2->name . "'s Team",
        ]);

        session(['current_team' => $team2]);

        $this->actingAs($user2);

        $this->component
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
})->group('livewire-components');

describe('Edge Cases', function() {
    it('long diagram names are handled correctly', function () {
        $longName = str_repeat('A', 255);

        /** @var Testcase $this */

        $this->component
            ->set('name', $longName)
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasNoErrors(['name']);

        expect(Diagram::where('name', $longName)->exists())->toBeTrue();
    });

    it('special characters in name are handled correctly', function () {
        $specialName = "Test Diagram: With & Special @Characters #123";

        /** @var Testcase $this */

        $this->component
            ->set('name', $specialName)
            ->set('style', 'colorful')
            ->set('database', 'mysql')
            ->call('create')
            ->assertHasNoErrors();

        expect(Diagram::where('name', $specialName)->exists())->toBeTrue();
    });
})->group('livewire-components');

