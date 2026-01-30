<?php

use App\Models\Team\Team;
use App\Livewire\BaseComponent;
use Livewire\Attributes\Validate;

new class extends BaseComponent {
    public string $name = '';
    public ?Team $team = null;

    #[Validate('required|string|current_password')]
    public string $password = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $team = Team::currentTeam();

        $this->authorize('view', $team);

        $this->team = Team::currentTeam();
        $this->name = $this->team->name ?? '';
    }

    public function leaveTeam(): void
    {
        $this->authorize('leaveTeam', $this->team);

        if (!Auth::user()->isAuthenticatedWithGoogleOrGithub() && is_null(Auth::user()->password)) {
            $this->validate();
        }

        $this->team->deleteUser(Auth::user());

        session()->forget('current_team');
        session()->regenerate();

        $this->alert(__('You have left the team successfully.'), type: 'success');
        $this->dispatch('team::team-left');

        $this->redirect(route('dashboard', absolute: true), navigate: true);
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
        <form wire:submit="updateTeamInformation" class="w-full space-y-6">
            <flux:input
                wire:model="name"
                :label="__('Team Name')"
                type="text"
                required
                :disabled="$team->owner->id !== auth()->user()->id"
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

            @can('updateTeam', $team)
                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit" class="w-full">
                        {{ __('Save') }}
                    </flux:button>

                    <x-action-message class="me-3" on="team-updated">
                        {{ __('Saved.') }}
                    </x-action-message>
                </div>
            @endcan
        </form>

        @can('leaveTeam', $team)
            <section class="mt-10 space-y-6">
                <div class="relative mb-5">
                    <flux:heading>{{ __('Leave team') }}</flux:heading>
                    <flux:subheading>{{ __('Leave the team and all of its resources') }}</flux:subheading>
                </div>

                <flux:modal.trigger name="confirm-leave-team">
                    <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-leave-team')" data-test="leave-team-button">
                        {{ __('Leave team') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal name="confirm-leave-team" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
                    <form method="POST" wire:submit="leaveTeam" class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('Are you sure you want to leave the team?') }}</flux:heading>

                            <flux:subheading>
                                {{ __('Once you leave the team, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently leave the team.') }}
                            </flux:subheading>
                        </div>

                        @if (!auth()->user()->isAuthenticatedWithGoogleOrGithub() && is_null(auth()->user()->password))
                            <flux:input wire:model="password" :label="__('Password')" type="password" />
                        @endif

                        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                            <flux:modal.close>
                                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>

                            <flux:button variant="danger" type="submit" data-test="confirm-leave-team-button">
                                {{ __('Leave team') }}
                            </flux:button>
                        </div>
                    </form>
                </flux:modal>
            </section>
        @endcan

    </x-settings.layout>
</section>
