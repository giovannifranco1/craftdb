<?php

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Livewire\BaseComponent;
use Livewire\Attributes\Computed;

new class extends BaseComponent {

    public function switchTeam(int $teamId): void
    {
        /** @var User */
        $user = Auth::user();

        $ownedTeams = $user->ownedTeams()->pluck('team.id');
        $memberTeams = $user->teams()->pluck('team.id');
        $allTeamIds = $ownedTeams->merge($memberTeams)->unique();

        if (!$allTeamIds->contains($teamId)) {
            $this->dispatch('alert', message: __('You do not have access to this team.'), type: 'error');
            return;
        }

        $team = Team::find($teamId);

        if ($team) {
            session(['current_team' => $team]);
            $this->dispatch('alert', message: __('Switched to team: ') . $team->name);
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    /**
     * @return Collection<int,Team>
    */
    #[Computed]
    public function getAllTeams(): Collection
    {
        /** @var User */
        $user = Auth::user();

        $ownedTeams = $user->ownedTeams()->get();
        $memberTeams = $user->teams()->get();
        $allTeams = $ownedTeams->merge($memberTeams)->unique('id')->sortBy('name');

        /** @var Collection<int, Team> $allTeams */
        return $allTeams;
    }
};
