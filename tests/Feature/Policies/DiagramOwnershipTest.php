<?php

namespace Tests\Feature\Policies;

use App\Models\Diagram;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->team = Team::factory()->create(['user_id' => $this->owner->id]);
    $this->diagram = Diagram::factory()->create(['team_id' => $this->team->id]);
    $this->stranger = User::factory()->create();
});

it('NOAUTH16: team owner can createOnTeam, update and share', function () {
    $gate = Gate::forUser($this->owner);

    expect($gate->allows('createOnTeam', [Diagram::class, $this->team]))->toBeTrue()
        ->and($gate->allows('update', $this->diagram))->toBeTrue()
        ->and($gate->allows('share', $this->diagram))->toBeTrue();
});

it('NOAUTH16: a user who does not own the team is denied', function () {
    $gate = Gate::forUser($this->stranger);

    expect($gate->allows('createOnTeam', [Diagram::class, $this->team]))->toBeFalse()
        ->and($gate->allows('update', $this->diagram))->toBeFalse()
        ->and($gate->allows('share', $this->diagram))->toBeFalse();
});
