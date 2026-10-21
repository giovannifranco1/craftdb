<?php

namespace Tests\Feature\Workspaces;

use App\Models\Diagram;
use App\Models\Team\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    /** @var \Tests\TestCase $this */
    $this->autenticated();
});

it('WS01: lists workspaces with diagram counts and marks the current one', function () {
    $other = $this->secondTeam();
    Diagram::factory()->count(2)->create(['team_id' => $other->id]);

    Livewire::test('settings.⚡workspaces')
        ->assertSee($this->team->name)
        ->assertSee('Second')
        ->assertSee(__('Current'))
        ->assertSeeInOrder(['Second', '2']);
});

it('WS02: creating a workspace makes it current', function () {
    Livewire::test('settings.⚡workspaces')
        ->set('createName', 'Brand new')
        ->call('create')
        ->assertHasNoErrors();

    $team = Team::where('name', 'Brand new')->firstOrFail();
    expect(session('current_team')->id)->toBe($team->id);
});

it('WS03: renames a workspace and refreshes the session when it is the current one', function () {
    $other = $this->secondTeam();

    Livewire::test('settings.⚡workspaces')
        ->call('startRename', $other->id)
        ->set('renameName', 'Second renamed')
        ->call('rename')
        ->assertHasNoErrors();
    expect($other->fresh()->name)->toBe('Second renamed');
    expect(session('current_team')->id)->toBe($this->team->id);

    Livewire::test('settings.⚡workspaces')
        ->call('startRename', $this->team->id)
        ->set('renameName', 'Current renamed')
        ->call('rename');
    expect(session('current_team')->name)->toBe('Current renamed');
});

it('WS04/WS08: deleting needs the exact name typed as confirmation', function () {
    $other = $this->secondTeam();
    $diagram = Diagram::factory()->create(['team_id' => $other->id]);

    Livewire::test('settings.⚡workspaces')
        ->call('confirmDelete', $other->id)
        ->set('confirmName', 'wrong')
        ->call('delete')
        ->assertHasErrors('confirmName');
    expect(Team::find($other->id))->not->toBeNull();

    Livewire::test('settings.⚡workspaces')
        ->call('confirmDelete', $other->id)
        ->set('confirmName', 'Second')
        ->call('delete')
        ->assertHasNoErrors();
    expect(Team::find($other->id))->toBeNull();
    expect(Diagram::find($diagram->id))->toBeNull();
});

it('WS05: deleting the only workspace shows an error and deletes nothing', function () {
    Livewire::test('settings.⚡workspaces')
        ->call('confirmDelete', $this->team->id)
        ->set('confirmName', $this->team->name)
        ->call('delete')
        ->assertHasErrors('team');

    expect(Team::find($this->team->id))->not->toBeNull();
});

it('WS06: deleting the current workspace switches the session to another', function () {
    $other = $this->secondTeam();

    Livewire::test('settings.⚡workspaces')
        ->call('confirmDelete', $this->team->id)
        ->set('confirmName', $this->team->name)
        ->call('delete')
        ->assertHasNoErrors();

    expect(Team::find($this->team->id))->toBeNull();
    expect(session('current_team')->id)->toBe($other->id);
});

it('WS07: makeCurrent switches the session and redirects to the dashboard', function () {
    $other = $this->secondTeam();

    Livewire::test('settings.⚡workspaces')
        ->call('makeCurrent', $other->id)
        ->assertRedirect(route('dashboard'));

    expect(session('current_team')->id)->toBe($other->id);
});

it('WS09: dashboard shows the current workspace name and a link to manage workspaces', function () {
    $this->get('/dashboard')
        ->assertOk()
        ->assertSee($this->team->name)
        ->assertSee(route('workspaces.index'), false);
});

it('WS11: a deleted session workspace is replaced by the first one with a notice', function () {
    $other = $this->secondTeam();
    session(['current_team' => $this->team]);
    $this->team->delete();

    $this->get('/dashboard')
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('message', __('The current workspace no longer exists. Switched to :name.', ['name' => 'Second']));

    expect(session('current_team')->id)->toBe($other->id);
});

it('WS12: settings navigation lists Workspaces and no Team Settings', function () {
    $this->get('/settings/profile')
        ->assertOk()
        ->assertSee('Workspaces')
        ->assertDontSee('Team Settings');
});

it('WS13: old team routes are gone and workspaces.index exists', function () {
    expect(Route::has('team.create'))->toBeFalse();
    expect(Route::has('team.edit'))->toBeFalse();
    expect(Route::has('dashboard.my-favorites'))->toBeFalse();
    expect(Route::has('workspaces.index'))->toBeTrue();
});

it("WS19: actions refuse another user's workspace id", function () {
    $foreign = $this->foreignTeam('Foreign');
    $notFound = \Illuminate\Database\Eloquent\ModelNotFoundException::class;

    $component = Livewire::test('settings.⚡workspaces');

    expect(fn () => $component->call('makeCurrent', $foreign->id))->toThrow($notFound);
    expect(fn () => $component->call('startRename', $foreign->id))->toThrow($notFound);
    expect(fn () => Livewire::test('settings.⚡workspaces')
        ->set('renamingId', $foreign->id)->set('renameName', 'Hacked')->call('rename'))->toThrow($notFound);
    expect(fn () => $component->call('confirmDelete', $foreign->id))->toThrow($notFound);
    expect(fn () => Livewire::test('settings.⚡workspaces')
        ->set('deletingId', $foreign->id)->set('confirmName', 'Foreign')->call('delete'))->toThrow($notFound);

    expect($foreign->fresh()->name)->toBe('Foreign');
    expect(session('current_team')->id)->toBe($this->team->id);
});
