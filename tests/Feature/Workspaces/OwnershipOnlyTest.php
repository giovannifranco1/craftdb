<?php

namespace Tests\Feature\Workspaces;

use App\Models\Column;
use App\Models\Diagram;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('WS18: team_user table no longer exists', function () {
    expect(Schema::hasTable('team_user'))->toBeFalse();
});

it('WS19: membership API is gone', function () {
    expect(method_exists(User::class, 'belongsToTeam'))->toBeFalse()
        ->and(method_exists(User::class, 'teams'))->toBeFalse()
        ->and(class_exists(\App\Models\Team\Membership::class))->toBeFalse();
});

it('WS19: only the owner can view and update a diagram of the workspace', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $diagram = Diagram::factory()->create(['team_id' => $team->id]);

    expect(Gate::forUser($owner)->allows('view', $diagram))->toBeTrue()
        ->and(Gate::forUser($owner)->allows('update', $diagram))->toBeTrue()
        ->and(Gate::forUser($stranger)->allows('view', $diagram))->toBeFalse();
});

it('WS20: Column::accessible returns columns of owned workspaces', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $diagram = Diagram::factory()->create(['team_id' => $team->id]);
    $table = Table::factory()->create(['diagram_id' => $diagram->id]);
    $column = Column::factory()->create(['table_id' => $table->id]);
    $other = Column::factory()->create();

    $ids = Column::query()->accessible($owner)->pluck('id');

    expect($ids)->toContain($column->id)->not->toContain($other->id);
});
