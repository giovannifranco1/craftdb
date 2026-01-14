<?php

namespace Tests;

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Livewire\Features\SupportTesting\Testable;

abstract class TestCase extends BaseTestCase
{
    protected User $user;
    protected Team $team;
    public Testable $component;

    public function autenticated(){
        $this->user = User::factory()->withoutTwoFactor()->create();
        $this->team = $this->user->ownedTeams()->create([
            'user_id' => $this->user->id,
            'name' => $this->user->name . "'s Team",
        ]);

        session(['current_team' => $this->team]);

        $this->actingAs($this->user);
    }
}
