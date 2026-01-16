<?php

use App\Actions\Team\CreateNewTeam;
use App\Models\Team\Team;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public string $name = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', Team::class);
    }

    /**
     * Create a new team.
     */
    public function createTeam(CreateNewTeam $action): void
    {
        $this->authorize('create', Team::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $team = $action->exec([
            'name' => $validated['name'],
            'user_id' => $user->id,
        ]);

        session(['current_team' => $team]);

        $this->dispatch('team-created', name: $team->name);
        $this->dispatch('alert', message: __('Team created successfully.'));

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Create Team')" :subheading="__('Create a new team to collaborate with others.')">
        <form wire:submit="createTeam" class="my-6 w-full space-y-6">
            <flux:input
                wire:model="name"
                :label="__('Team Name')"
                :placeholder="__('Enter team name')"
                type="text"
                required
                autofocus
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Create Team') }}
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
