<?php

use App\Models\Team\Team;
use App\Livewire\BaseComponent;

new class extends BaseComponent {
    public string $name = '';
    public ?Team $team = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('updateTeamMember', Team::currentTeam());

        $this->team = Team::currentTeam();
        $this->name = $this->team->name ?? '';
    }

    /**
     * Update the team information.
     */
    public function updateTeamInformation(): void
    {
        $this->authorize('updateTeam', Team::currentTeam());

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->team->update($validated);

        $this->dispatch('team-updated', name: $this->team->name);
        $this->dispatch('alert', message: __('Team information updated successfully.'));
    }
}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Team Settings')" :subheading="__('The team\'s name and owner information.')">
        <form wire:submit="updateTeamInformation" class="my-6 w-full space-y-6">
            <flux:input
                wire:model="name"
                :label="__('Team Name')"
                type="text"
                required
                autofocus
            />

            <div class="space-y-2">
                <flux:label>{{ __('Team Owner') }}</flux:label>
                <div class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-3">
                    <flux:avatar
                        size="sm"
                        :initials="$team->owner->initials()"
                    />
                    <div>
                        <div class="font-medium text-sm">{{ $team->owner->name }}</div>
                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $team->owner->email }}</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" class="w-full">
                    {{ __('Save') }}
                </flux:button>

                <x-action-message class="me-3" on="team-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
