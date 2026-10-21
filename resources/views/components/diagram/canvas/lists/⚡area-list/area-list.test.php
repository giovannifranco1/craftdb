<?php

use App\Models\Diagram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram} $this */

    $this->autenticated();

    Livewire::withoutLazyLoading();

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->component = Livewire::test('diagram.canvas.lists.⚡area-list', ['diagram' => $this->diagram]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
});

describe('Area Management', function() {
    it('can add an area', function () {
        /** @var TestCase&object{diagram: Diagram} $this */

        $this->component
            ->call('addArea', 100, 100)
            ->assertDispatched('area::added');

        $this->assertDatabaseHas('area', [
            'diagram_id' => $this->diagram->id,
            'x' => 100,
            'y' => 100,
            'width' => 400,
            'height' => 300,
        ]);
    });
})->group('livewire-components');
