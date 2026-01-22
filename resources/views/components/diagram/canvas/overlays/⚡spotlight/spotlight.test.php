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
    $this->table = Table::factory()->create(['diagram_id' => $this->diagram->id, 'name' => 'users']);
    $this->component = Livewire::test('diagram.⚡spotlight', ['diagram' => $this->diagram]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
})->group('livewire-components');

describe('Search Functionality', function() {
    it('searches for tables', function () {
        /** @var TestCase $this */

        $this->component->set('search', 'users');

        expect($this->component->get('results')->count())->toBeGreaterThan(0);
    });

    it('dispatches event when table is selected', function () {
        /** @var TestCase&object{table: Table} $this */

        $this->component
            ->call('selectTable', $this->table->id)
            ->assertDispatched('spotlight::table-selected');
    });
})->group('livewire-components');
