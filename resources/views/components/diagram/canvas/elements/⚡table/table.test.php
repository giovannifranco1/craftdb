<?php

use App\Models\Diagram;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram, table: Table} $this */

    $this->autenticated();

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->table = Table::factory()->create(['diagram_id' => $this->diagram->id]);
    $this->component = Livewire::test('diagram.canvas.elements.⚡table', ['table' => $this->table]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
})->group('livewire-components');

describe('Table Management', function() {
    it('can rename table', function () {
        /** @var TestCase&object{table: Table} $this */

        $this->component
            ->set('name', 'New Name')
            ->call('renameTable')
            ->assertDispatched('table::renamed');

        expect($this->table->fresh()->name)->toBe('New Name');
    });

    it('can add column', function () {
        /** @var TestCase&object{table: Table} $this */

        $this->component
            ->call('addColumn')
            ->assertDispatched("table::column-added.{$this->table->id}");

        expect($this->table->columns()->count())->toBe(1);
    });

    it('can remove table', function () {
        /** @var TestCase&object{table: Table} $this */

        $this->component
            ->call('remove')
            ->assertDispatched('table::removed');

        expect(Table::find($this->table->id))->toBeNull();
    });
})->group('livewire-components');
