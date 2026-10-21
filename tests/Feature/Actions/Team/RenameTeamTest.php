<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\RenameTeam;
use App\Models\Team\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('WS03: renames the workspace', function () {
    $team = Team::factory()->create(['name' => 'Old']);

    $result = (new RenameTeam())->exec($team, 'New name');

    expect($result->name)->toBe('New name')
        ->and($team->fresh()->name)->toBe('New name');
});

it('WS03: rejects an empty or too long name', function (string $name) {
    $team = Team::factory()->create(['name' => 'Old']);

    expect(fn () => (new RenameTeam())->exec($team, $name))->toThrow(ValidationException::class);
    expect($team->fresh()->name)->toBe('Old');
})->with(['empty' => '', 'too long' => fn () => str_repeat('a', 256)]);
