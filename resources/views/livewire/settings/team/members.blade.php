<?php

use App\Models\Team\Team;
use App\Models\Team\Membership;
use App\Models\Team\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Volt\Component;

new class extends Component {
    public ?Team $team = null;
    public string $email = '';
    public int $role_id = 0;

    public function mount(): void
    {
        $this->authorize('addTeamMember', Team::currentTeam());

        $this->team = Team::currentTeam();
        $roles = $this->team->roles;
        $this->role_id = $roles->first()?->id ?? 0;
    }

    public function addMember(): void
    {
        $this->authorize('addTeamMember', Team::currentTeam());

        $validated = $this->validate(   [
            'email' => ['required', 'email', 'exists:users,email'],
            'role_id' => ['required', 'exists:role,id'],
        ], [
            'email.exists' => __('No user found with this email address.'),
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Check if user is already a member
        if ($this->team->users()->where('users.id', $user->id)->exists()) {
            $this->addError('email', __('This user is already a member of the team.'));
            return;
        }

        // Does not allow adding the team owner as a member again
        if ($user->id === $this->team->user_id) {
            $this->addError('email', __('You cannot add the team owner as a member.'));
            return;
        }

        $this->team->users()->attach($user->id, ['role_id' => $validated['role_id']]);

        $this->reset('email', 'role_id');
        $this->dispatch('alert', message: __('Team member added successfully.'));
        $this->dispatch('member-added');
    }

    public function removeMember(int $userId): void
    {
        $this->authorize('removeTeamMember', Team::currentTeam());

        if ($this->team->user_id === $userId) {
            $this->dispatch('alert', message: __('You cannot remove the team owner.'), type: 'error');
            return;
        }

        $this->team->users()->detach($userId);
        $this->dispatch('alert', message: __('Team member removed successfully.'));
        $this->dispatch('member-removed');
    }

    public function updateRole(int $userId, int $roleId): void
    {
        $this->authorize('updateTeamMember', Team::currentTeam());

        $membership = Membership::where('team_id', $this->team->id)
            ->where('user_id', $userId)
            ->first();

        if ($membership) {
            $membership->update(['role_id' => $roleId]);
            $this->dispatch('alert', message: __('Member role updated successfully.'));
        }
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Team Members')" :subheading="__('All of the people that are part of this team.')">
        <!-- Add Member Form -->
        <div class="my-6">
            <form wire:submit="addMember" class="space-y-4">
                <flux:input
                    wire:model="email"
                    :label="__('Email Address')"
                    type="email"
                    required
                    :placeholder="__('member@example.com')"
                />

                <div>
                    <flux:label>{{ __('Role') }}</flux:label>
                    @php
                        $editorRole = $team->roles->firstWhere('code', 'editor');
                        $viewerRole = $team->roles->firstWhere('code', 'viewer');
                    @endphp
                    <div class="mt-2 space-y-2">
                        @if($editorRole)
                            <label
                                    @class([
                                        'flex items-start gap-3 p-3 rounded-lg border cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors',
                                        'border-blue-500 bg-blue-50 dark:bg-blue-950/20' => $role_id == $editorRole->id,
                                        'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700' => $role_id != $editorRole->id,
                                    ])
                                >
                                <input
                                    type="radio"
                                    wire:model.live="role_id"
                                    value="{{ $editorRole->id }}"
                                    class="mt-1"
                                    required
                                />
                                <div class="flex-1">
                                    <div class="font-medium text-sm">{{ __('Editor') }}</div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ __('Can create, edit and view diagrams') }}
                                    </div>
                                </div>
                            </label>
                        @endif

                        @if($viewerRole)
                            <label
                                    @class([
                                        'flex items-start gap-3 p-3 rounded-lg border cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors',
                                        'border-blue-500 bg-blue-50 dark:bg-blue-950/20' => $role_id == $viewerRole->id,
                                        'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700' => $role_id != $viewerRole->id,
                                    ])
                                >
                                <input
                                    type="radio"
                                    wire:model.live="role_id"
                                    value="{{ $viewerRole->id }}"
                                    class="mt-1"
                                />
                                <div class="flex-1">
                                    <div class="font-medium text-sm">{{ __('Viewer') }}</div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ __('Can only view diagrams') }}
                                    </div>
                                </div>
                            </label>
                        @endif
                    </div>
                </div>

                <flux:button variant="primary" type="submit">
                    {{ __('Add Member') }}
                </flux:button>
            </form>
        </div>

        <flux:separator class="my-6" />

        <!-- Members List -->
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Current Members') }}</flux:heading>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-3">
                        <flux:avatar
                            size="sm"
                            :initials="$team->owner->initials()"
                        />
                        <div>
                            <div class="font-medium text-sm">
                                {{ $team->owner->name }}
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $team->owner->email }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:badge color="blue" size="sm">{{ __('Team Owner') }}</flux:badge>
                    </div>
                </div>

                @foreach($team->users()->get() as $user)

                    @php
                        $currentRoleId = $user->membership->role_id;
                    @endphp

                    <div class="flex items-center justify-between p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-3">
                            <flux:avatar
                                size="sm"
                                :initials="$user->initials()"
                            />
                            <div>
                                <div class="font-medium text-sm">
                                    {{ $user->name }}
                                    @if($user->id === $team->user_id)
                                        <flux:badge color="blue" size="sm" class="ml-2">{{ __('Owner') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <flux:select
                                wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                size="sm"
                                class="w-34"
                            >
                                @foreach($team->roles as $role)
                                    <option
                                        value="{{ $role->id }}"
                                        @if($currentRoleId === $role->id) selected @endif
                                    >
                                        {{ __($role->name ?? $role->code) }}
                                    </option>
                                @endforeach
                            </flux:select>

                            <flux:button
                                wire:click="removeMember({{ $user->id }})"
                                wire:confirm="{{ __('Are you sure you want to remove this member?') }}"
                                variant="danger"
                                size="sm"
                            >
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-settings.layout>
</section>
