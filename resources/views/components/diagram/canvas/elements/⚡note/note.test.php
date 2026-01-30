<?php

use App\Models\Diagram;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram, note: Note} $this */

    $this->autenticated();

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->note = Note::factory()->create(['diagram_id' => $this->diagram->id]);

    $this->component = Livewire::test('diagram.note', ['note' => $this->note]);
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });
})->group('livewire-components');

describe('Note Management', function() {
    it('can update note content', function () {
        /** @var TestCase&object{note: Note} $this */

        $this->component
            ->set('content', 'New content')
            ->call('updateContent')
            ->assertDispatched('note::content-updated');

        expect($this->note->fresh()->content)->toBe('New content');
    });

    it('can remove note', function () {
        /** @var TestCase&object{note: Note} $this */

        $this->component
            ->call('remove')
            ->assertDispatched('note::removed');

        expect(Note::find($this->note->id))->toBeNull();
    });
})->group('livewire-components');
