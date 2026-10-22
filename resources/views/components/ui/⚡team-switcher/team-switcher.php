<?php

use App\Models\Team\Team;
use App\Models\User;
use App\Livewire\BaseComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

new class extends BaseComponent {

    public function switchTeam(int $teamId): void
    {
        /** @var User */
        $user = Auth::user();

        $team = $user->ownedTeams()->find($teamId);

        if (! $team) {
            $this->dispatch('alert', message: __('You do not have access to this workspace.'), type: 'error');
            return;
        }

        session(['current_team' => $team]);

        $this->dispatch('alert', message: __('Switched to workspace: ') . $team->name);
        $this->redirect(route('dashboard'), navigate: true);
    }

    #[Computed]
    public function currentTeam(): ?Team
    {
        return Team::currentTeam();
    }

    /**
     * @return Collection<int, Team>
     */
    #[Computed]
    public function getAllTeams(): Collection
    {
        return Auth::user()->allTeams();
    }
};
