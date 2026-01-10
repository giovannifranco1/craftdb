<?php

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;


new class extends Component {
    public function switchTeam(int $teamId): void
    {
        /** @var User */
        $user = auth()->user();

        // Get all teams the user has access to (owned + member)
        $ownedTeams = $user->ownedTeams()->pluck('team.id');
        $memberTeams = $user->teams()->pluck('team.id');
        $allTeamIds = $ownedTeams->merge($memberTeams)->unique();

        // Check if user has access to this team
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

    #[Computed(persist: true)]
    public function getAllTeams()
    {
        /** @var User */
        $user = auth()->user();
        $currentTeam = Team::currentTeam();

        // Get all teams (owned + member)
        $ownedTeams = $user->ownedTeams()->get();
        $memberTeams = $user->teams()->get();

        $allTeams = $ownedTeams->merge($memberTeams)->unique('id')->sortBy('name');

        return $allTeams;
    }
}; ?>

<div>
    @if($this->getAllTeams->count() > 1)
        <flux:menu.radio.group>
            <div class="px-2 py-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                {{ __('Switch Team') }}
            </div>

            @foreach($this->getAllTeams as $team)
                <flux:menu.item
                    wire:click="switchTeam({{ $team->id }})"
                    class="cursor-pointer"
                >
                    <div class="flex items-center justify-between w-full">
                        <span>{{ $team->name }}</span>
                        @if($currentTeam && $currentTeam->id === $team->id)
                            <flux:icon.check-circle class="size-4 text-[#15B8A5]" />
                        @endif
                    </div>
                </flux:menu.item>
            @endforeach
        </flux:menu.radio.group>

        <flux:menu.separator />
    @endif
</div>
