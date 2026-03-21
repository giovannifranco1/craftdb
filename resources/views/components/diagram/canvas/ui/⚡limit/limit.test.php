<?php

use App\Models\Diagram;
use App\Support\DiagramLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var TestCase&object{diagram: Diagram} $this */

    $this->autenticated();

    // Team owner is resolved from the DB for DiagramLimits; mock Auth alone is not enough.
    $this->user->subscriptions()->create([
        'type' => config('billing.subscription_name', 'default'),
        'stripe_id' => 'sub_test_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => config('billing.plans.pro_usd.price_id'),
    ]);

    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->component = Livewire::test('diagram.canvas.ui.⚡limit', ['diagram' => $this->diagram]);
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

        $proLimit = DiagramLimits::forUser($this->user)['table_limit'];

        $this->component->refresh();
        $this->component->assertSet('limit', $proLimit);
        $this->component->assertSee('2 / '.$proLimit);

        expect($this->component->get('tableCount'))->toBe(2);
    });
});
