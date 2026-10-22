<?php

namespace Tests\Feature\Workspaces;

use App\Actions\Diagram\AddRelationship;
use App\Models\Diagram;
use App\Models\Note;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->autenticated();
    $this->teamB = $this->user->ownedTeams()->create(['name' => 'Workspace B']);
    $this->diagramB = Diagram::factory()->create(['team_id' => $this->teamB->id, 'database' => 'mysql']);
});

it('WS14: updates a note and adds a relationship in a diagram of a non-current workspace', function () {
    $note = Note::factory()->create(['diagram_id' => $this->diagramB->id]);

    Livewire::test('diagram.canvas.elements.⚡note', ['note' => $note])
        ->set('content', 'From B')
        ->call('updateContent')
        ->assertOk();
    expect($note->fresh()->content)->toBe('From B');

    $t1 = Table::factory()->create(['diagram_id' => $this->diagramB->id]);
    $t2 = Table::factory()->create(['diagram_id' => $this->diagramB->id]);

    $result = (new AddRelationship())->exec([
        'fromTableId' => $t1->id,
        'toTableId' => $t2->id,
        'type' => '1:n',
    ]);

    expect($result)->not->toBeNull();
});

it('WS15: diagram page of another workspace links to its own workspace and shows its name', function () {
    $this->get(route('diagram.show', [$this->teamB->id, $this->diagramB->slug]))
        ->assertOk()
        ->assertSee("/{$this->teamB->id}/diagram/{$this->diagramB->slug}", false)
        ->assertSee('Workspace B')
        ->assertDontSee("/{$this->team->id}/diagram/{$this->diagramB->slug}", false);
});

it('WS16: menu counter follows the current workspace without leaking cache', function () {
    Diagram::factory()->count(2)->create(['team_id' => $this->team->id, 'database' => 'mysql']);
    Diagram::factory()->count(3)->create(['team_id' => $this->teamB->id, 'database' => 'mysql']);

    $badge = fn () => (string) \Illuminate\Support\Facades\Blade::render('<x-ui.menu />');

    $countA = Diagram::where('team_id', $this->team->id)->count();
    $countB = Diagram::where('team_id', $this->teamB->id)->count();
    expect($countA)->not->toBe($countB);

    expect($badge())->toContain(">{$countA}<");
    Livewire::test('pages::dashboard');

    session(['current_team' => $this->teamB]);
    Livewire::test('pages::dashboard');
    expect($badge())->toContain(">{$countB}<")->not->toContain(">{$countA}<");
});

it('WS17: toggleFavorite is denied on another user diagram and toggles on own', function () {
    $foreign = Diagram::factory()->create(['database' => 'mysql', 'team_id' => $this->foreignTeam('X')->id]);
    $own = Diagram::factory()->create(['team_id' => $this->team->id, 'is_favorite' => false, 'database' => 'mysql']);

    Livewire::test('pages::dashboard')
        ->call('toggleFavorite', $foreign->id)
        ->assertDispatched('alert', type: 'error');
    expect($foreign->fresh()->is_favorite)->toBeFalsy();

    Livewire::test('pages::dashboard')->call('toggleFavorite', $own->id);
    expect($own->fresh()->is_favorite)->toBeTruthy();
});

it('WS14: AddRelationship denies tables of another user diagram', function () {
    $otherTeam = $this->foreignTeam('Other');
    $other = Diagram::factory()->create(['team_id' => $otherTeam->id, 'database' => 'mysql']);
    $t1 = Table::factory()->create(['diagram_id' => $other->id]);
    $t2 = Table::factory()->create(['diagram_id' => $other->id]);

    expect(fn () => (new AddRelationship())->exec([
        'fromTableId' => $t1->id,
        'toTableId' => $t2->id,
        'type' => '1:n',
    ]))->toThrow(\Exception::class, "One or both tables do not belong to the user's workspace.");

    expect(\App\Models\Relationship::query()->count())->toBe(0);
});

it('WS17: toggle-favorite route denies another user diagram', function () {
    $otherTeam = $this->foreignTeam('Other');
    $foreign = Diagram::factory()->create(['team_id' => $otherTeam->id, 'is_favorite' => false, 'database' => 'mysql']);

    $this->post(route('diagram.toggle-favorite', [$otherTeam->id, $foreign->slug]))
        ->assertForbidden();

    expect($foreign->fresh()->is_favorite)->toBeFalsy();
});
