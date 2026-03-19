<?php

use App\Models\Column;
use App\Models\Diagram;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram, table: Table} $this */

    $this->autenticated();

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->table = Table::factory()->create(['diagram_id' => $this->diagram->id]);

    $this->component = Livewire::test('diagram.canvas.overlays.⚡side-editor', ['diagram' => $this->diagram]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
});

describe('Column Management', function() {
    it('can add a column', function () {
        /** @var TestCase&object{table: Table} $this */

        $this->component
            ->dispatch('table-side-editor::open', $this->table->id)
            ->assertSet('tableId', $this->table->id)
            ->call('addColumn')
            ->assertDispatched('table-side-editor::column-added');

        expect($this->table->columns()->count())->toBe(1);
    });

    it('can update a column', function () {
        /** @var TestCase&object{table: Table} $this */

        $column = $this->table->columns()->create([
            'name' => 'old_name',
            'type' => 'varchar',
            'order' => 1,
        ]);

        $this->component = Livewire::test('diagram.canvas.overlays.⚡side-editor', ['diagram' => $this->diagram]);
        $this->component
            ->dispatch('table-side-editor::open', $this->table->id)
            ->assertSet('tableId', $this->table->id)
            ->set('columnForm.name', 'new_name')
            ->call('updateColumn', $column->id)
            ->assertDispatched('table-side-editor::column-updated');

        expect($column->fresh()->name)->toBe('new_name');
    });

    it('can delete a column by id', function () {
        /** @var TestCase&object{table: Table} $this */

        $column = $this->table->columns()->create([
            'name' => 'test_column',
            'type' => 'varchar',
            'order' => 1,
        ]);

        $this->component
            ->dispatch('table-side-editor::open', $this->table->id)
            ->assertSet('tableId', $this->table->id)
            ->call('deleteColumnById', $column->id)
            ->assertDispatched('table-side-editor::column-deleted');

        expect(Column::find($column->id))->toBeNull();
    });

    it('can toggle primary key', function () {
        /** @var TestCase&object{table: Table} $this */

        $column = $this->table->columns()->create([
            'name' => 'test_column',
            'type' => 'integer',
            'order' => 1,
            'is_primary' => false,
        ]);

        $this->component
            ->dispatch('table-side-editor::open', $this->table->id)
            ->assertSet('tableId', $this->table->id)
            ->call('togglePrimaryKey', $column->id)
            ->assertDispatched('table-side-editor::column-saved');

        expect($column->fresh()->is_primary)->toBe(1, 'Column is not primary key');
    });

    it('can select a column', function () {
        /** @var TestCase&object{table: Table} $this */

        $column = $this->table->columns()->create([
            'name' => 'test_column',
            'type' => 'varchar',
            'order' => 1,
        ]);

        $this->component
            ->dispatch('table-side-editor::open', $this->table->id)
            ->assertSet('tableId', $this->table->id)
            ->call('selectColumn', $column->id);

        expect($this->component->get('selectedColumnId'))->toBe($column->id);
    });
});
