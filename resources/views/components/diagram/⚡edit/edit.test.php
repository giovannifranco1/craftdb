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
    $this->component = Livewire::test('diagram.⚡edit', ['diagram' => $this->diagram]);
});

describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
})->group('livewire-components');

describe('Diagram Update', function() {
    it('can update diagram information', function () {
        /** @var TestCase&object{diagram: Diagram} $this */

        $this->component
            ->set('name', 'New Diagram Name')
            ->set('description', 'New description')
            ->call('update')
            ->assertDispatched('diagram-updated');

        $this->diagram->refresh();

        expect($this->diagram->name)->toBe('New Diagram Name');
        expect($this->diagram->description)->toBe('New description');
    });

    it('shows warning when changing style or database', function () {
        /** @var TestCase $this */

        $this->component->set('style', $this->component->get('style') === 'colorful' ? 'neutral' : 'colorful');

        expect($this->component->get('showWarning'))->toBe(true);
    });
})->group('livewire-components');

describe('Step Navigation', function() {
    it('navigates through steps', function () {
        /** @var TestCase $this */

        $this->component
            ->call('nextStep')
            ->assertSet('step', 2)
            ->call('prevStep')
            ->assertSet('step', 1);
    });
})->group('livewire-components');
