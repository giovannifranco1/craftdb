<?php

namespace Tests\Feature\Actions\Team;

use App\Actions\Team\CreateNewTeam;
use App\Models\Team\Role;
use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(RefreshDatabase::class);

/**
 * @property User $user
 * @property CreateNewTeam $action
 */
beforeEach(function (): void {
    /** @var TestCase $this */
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->action = new CreateNewTeam();
});

describe('Team creation', function (): void {
    it('can create a new team', function () {
        /** @var TestCase $this */
        $team = $this->action->exec([
            'name' => 'My Team',
            'user_id' => $this->user->id,
        ]);

        expect($team)->toBeInstanceOf(Team::class)
            ->and($team->name)->toBe('My Team')
            ->and($team->user_id)->toBe($this->user->id)
            ->and($team->exists)->toBeTrue();

        // Verify team is in database
        /** @phpstan-ignore-next-line */
        $this->assertDatabaseHas('team', [
            'name' => 'My Team',
            'user_id' => $this->user->id,
        ]);
    });

    it('creates default roles when team is created', function () {
        /** @var TestCase $this */
        $team = $this->action->exec([
            'name' => 'My Team',
            'user_id' => $this->user->id,
        ]);

        // Check editor role
        $editorRole = Role::where('team_id', $team->id)
            ->where('code', 'editor')
            ->first();

        expect($editorRole)->not->toBeNull();

        if ($editorRole) {
            expect($editorRole->name)->toBe('Editor')
                ->and($editorRole->description)->toBe('Can create, edit and view diagrams');
        }

        // Check viewer role
        $viewerRole = Role::where('team_id', $team->id)
            ->where('code', 'viewer')
            ->first();

        expect($viewerRole)->not->toBeNull();

        if ($viewerRole) {
            expect($viewerRole->name)->toBe('Viewer')
                ->and($viewerRole->description)->toBe('Can only view diagrams');
        }
    });
});

describe('Validation', function (): void {
    it('requires name', function () {
        /** @var TestCase $this */
        $this->action->exec([
            'user_id' => $this->user->id,
        ]);
    })->throws(ValidationException::class);

    it('requires name to be string', function () {
        /** @var TestCase $this */
        $this->action->exec([
            'name' => 12345,
            'user_id' => $this->user->id,
        ]);
    })->throws(ValidationException::class);

    it('requires name to be max 255 characters', function () {
        /** @var TestCase $this */
        $this->action->exec([
            'name' => str_repeat('a', 256),
            'user_id' => $this->user->id,
        ]);
    })->throws(ValidationException::class);

    it('requires user_id', function () {
        /** @var TestCase $this */
        $this->action->exec([
            'name' => 'My Team',
        ]);
    })->throws(ValidationException::class);

    it('requires user_id to be integer', function () {
        /** @var TestCase $this */
        $this->action->exec([
            'name' => 'My Team',
            'user_id' => 'invalid',
        ]);
    })->throws(ValidationException::class);

    it('requires user_id to exist in users table', function () {
        /** @var TestCase $this */
        $this->action->exec([
            'name' => 'My Team',
            'user_id' => 99999,
        ]);
    })->throws(ValidationException::class);

    it('accepts valid data', function () {
        /** @var TestCase $this */
        $team = $this->action->exec([
            'name' => 'Valid Team Name',
            'user_id' => $this->user->id,
        ]);

        expect($team)->toBeInstanceOf(Team::class)
            ->and($team->name)->toBe('Valid Team Name')
            ->and($team->user_id)->toBe($this->user->id);
    });
});
