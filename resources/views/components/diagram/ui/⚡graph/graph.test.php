<?php

use App\Models\Diagram;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */

    $this->autenticated();

    // Create test diagram for component
    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);

    $this->component = Livewire::test('diagram.ui.⚡graph', ['diagram' => $this->diagram]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
});

describe('Graph Display', function() {
    it('displays relationships', function () {
        /** @var TestCase $this */

        expect($this->component->get('diagram')->id)->toBe($this->diagram->id);
    });

    it('refreshes graph on events', function () {
        /** @var TestCase $this */

        $this->component
            ->call('refreshGraph')
            ->assertDispatched('diagram-graph::refresh');
    });
});
