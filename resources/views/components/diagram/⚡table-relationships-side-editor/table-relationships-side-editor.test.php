<?php

use App\Models\Diagram;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */

    $this->autenticated();

    // Create test diagram and table for component
    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->table = Table::factory()->create(['diagram_id' => $this->diagram->id]);

    $this->component = Livewire::test('diagram.⚡table-relationships-side-editor', ['table' => $this->table]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
});

describe('Relationships Display', function() {
    it('displays relationships for a table', function () {
        /** @var TestCase $this */

        expect($this->component->get('table')->id)->toBe($this->table->id);
    });
});
