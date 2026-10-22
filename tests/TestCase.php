<?php

namespace Tests;

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;

abstract class TestCase extends BaseTestCase
{
    protected User $user;

    protected Team $team;

    /** @var Testable<Component> */
    public $component;

    public function autenticated()
    {
        $this->user = User::factory()->create();
        $this->team = $this->user->ownedTeams()->create([
            'name' => $this->user->name."'s Team",
        ]);

        session(['current_team' => $this->team]);

        $this->actingAs($this->user);
    }

    public function secondTeam(string $name = 'Second'): Team
    {
        return $this->user->ownedTeams()->create(['name' => $name]);
    }

    public function foreignTeam(string $name = 'Foreign'): Team
    {
        return User::factory()->create()->ownedTeams()->create(['name' => $name]);
    }
}
