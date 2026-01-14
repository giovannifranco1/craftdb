<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase $this */

    $this->component = Livewire::test('diagram.side-panel');
});


describe('Rendering & Initialization', function() {
    it('renders successfully', function () {
        /** @var TestCase $this */

        $this->component->assertOk();
    });

    it('has default properties', function () {
        /** @var TestCase $this */

        expect($this->component->get('open'))->toBe(false);
        expect($this->component->get('title'))->toBe(null);
        expect($this->component->get('component'))->toBe(null);
    });
})->group('livewire-components');

describe('Side Panel State', function() {
    it('can be opened and closed', function () {
        /** @var TestCase $this */

        $this->component->set('open', true);
        expect($this->component->get('open'))->toBe(true);

        $this->component->set('open', false);
        expect($this->component->get('open'))->toBe(false);
    });
})->group('livewire-components');
