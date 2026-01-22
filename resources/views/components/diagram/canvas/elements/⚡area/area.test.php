<?php

use App\Models\Area;
use App\Models\Diagram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram, area: Area} $this */

    $this->autenticated();

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->area = Area::factory()->create(['diagram_id' => $this->diagram->id]);
    $this->component = Livewire::test('diagram.area', ['area' => $this->area]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
})->group('livewire-components');

describe('Area Management', function() {
    it('can rename area', function () {
        /** @var TestCase&object{area: Area} $this */

        $this->component
            ->set('name', 'New Name')
            ->call('renameArea')
            ->assertDispatched('area::renamed');

        expect($this->area->fresh()->name)->toBe('New Name');
    });

    it('can remove area', function () {
        /** @var TestCase&object{area: Area} $this */

        $this->component
            ->call('remove')
            ->assertDispatched('area::removed');

        expect(Area::find($this->area->id))->toBeNull();
    });
})->group('livewire-components');
