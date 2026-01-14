<?php

use App\Models\Diagram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram} $this */

    $this->autenticated();

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->component = Livewire::test('diagram.table.limit', ['diagram' => $this->diagram]);
});

describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
});

describe('Table Count', function() {
    it('displays table count', function () {
        /** @var TestCase&object{diagram: Diagram} $this */

        $this->diagram->tables()->create(['name' => 'users', 'x' => 0, 'y' => 0]);
        $this->diagram->tables()->create(['name' => 'posts', 'x' => 0, 'y' => 0]);

        $this->component->refresh();
        $this->component->assertSet('limit', 30);
        $this->component->assertSee('2 / 30');

        expect($this->component->get('getTableCount'))->toBe(2);
    });
});
