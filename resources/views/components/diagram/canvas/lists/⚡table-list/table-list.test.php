<?php

use App\Models\Diagram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram} $this */

    $this->autenticated();

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->component = Livewire::test('diagram.table-list', ['diagram' => $this->diagram]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
})->group('livewire-components');

describe('Table Management', function() {
    it('can add a table', function () {
        /** @var TestCase&object{diagram: Diagram} $this */

        $this->component
            ->call('addTable', 100, 100)
            ->assertDispatched('table::added');

        $this->assertDatabaseHas('table', [
            'diagram_id' => $this->diagram->id,
            'x' => 100,
            'y' => 100,
        ]);
    });

    it('prevents adding tables beyond limit', function () {
        /** @var TestCase&object{diagram: Diagram} $this */

        // Create tables up to the limit
        for ($i = 0; $i < Diagram::TABLE_LIMIT; $i++) {
            $this->diagram->tables()->create([
                'name' => "table_$i",
                'x' => 0,
                'y' => 0,
            ]);
        }

        $this->component
            ->call('addTable', 100, 100)
            ->assertDispatched('alert');

        expect($this->diagram->tables()->count())->toBe(Diagram::TABLE_LIMIT);
    });
})->group('livewire-components');
