<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\DeleteTeam;
use App\Models\Area;
use App\Models\Column;
use App\Models\Diagram;
use App\Models\Note;
use App\Models\Relationship;
use App\Models\Table;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function seedWorkspace(Team $team): void
{
    foreach (range(1, 2) as $i) {
        $diagram = Diagram::factory()->create(['team_id' => $team->id]);
        $from = Table::factory()->create(['diagram_id' => $diagram->id]);
        $to = Table::factory()->create(['diagram_id' => $diagram->id]);
        $fromCol = Column::factory()->create(['table_id' => $from->id]);
        $toCol = Column::factory()->create(['table_id' => $to->id]);
        Relationship::factory()->between($from, $fromCol, $to, $toCol)->oneToMany()->create();
        Area::factory()->create(['diagram_id' => $diagram->id]);
        Note::factory()->create(['diagram_id' => $diagram->id]);
    }
}

function countsFor(Team $team): array
{
    $diagramIds = Diagram::where('team_id', $team->id)->pluck('id');
    $tableIds = Table::whereIn('diagram_id', $diagramIds)->pluck('id');

    return [
        'team' => Team::whereKey($team->id)->count(),
        'diagrams' => $diagramIds->count(),
        'tables' => $tableIds->count(),
        'columns' => Column::whereIn('table_id', $tableIds)->count(),
        'relationships' => Relationship::whereIn('from_table_id', $tableIds)->count(),
        'areas' => Area::whereIn('diagram_id', $diagramIds)->count(),
        'notes' => Note::whereIn('diagram_id', $diagramIds)->count(),
    ];
}

it('WS04: deletes the workspace with all its content and keeps the other one intact', function () {
    $user = User::factory()->create();
    $doomed = Team::factory()->create(['user_id' => $user->id]);
    $kept = Team::factory()->create(['user_id' => $user->id]);
    seedWorkspace($doomed);
    seedWorkspace($kept);
    $keptBefore = countsFor($kept);

    expect(countsFor($doomed)['diagrams'])->toBe(2);

    app(DeleteTeam::class)->exec($doomed);

    expect(countsFor($doomed))->each->toBe(0)
        ->and(countsFor($kept))->toBe($keptBefore)
        ->and($keptBefore['relationships'])->toBe(2);
});

it('WS05: refuses to delete the only workspace', function () {
    $user = User::factory()->create();
    $only = Team::factory()->create(['user_id' => $user->id]);
    seedWorkspace($only);
    $before = countsFor($only);

    try {
        app(DeleteTeam::class)->exec($only);
        $this->fail('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('team');
    }

    expect(countsFor($only))->toBe($before);
});
