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
    $this->component = Livewire::test('diagram.canvas.lists.⚡note-list', ['diagram' => $this->diagram]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
})->group('livewire-components');

describe('Note Management', function() {
    it('can add a note', function () {
        /** @var TestCase&object{diagram: Diagram} $this */

        $this->component
            ->call('addNote', 100, 100)
            ->assertDispatched('note::added');

        $this->assertDatabaseHas('note', [
            'diagram_id' => $this->diagram->id,
            'x' => 100,
            'y' => 100,
            'width' => 175,
            'height' => 75,
        ]);
    });
})->group('livewire-components');
