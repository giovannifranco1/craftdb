<?php

use App\Enums\Role;
use App\Models\Team\Team;
use App\Models\User;
use App\Livewire\BaseComponent;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use App\Models\Team\Invitation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;

new class extends BaseComponent {
    #[Locked]
    public Team $team;

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|exists:role,code')]
    public Role $roleChoosen;

    public Role $editorRole = Role::EDITOR;
    public Role $viewerRole = Role::VIEWER;

    /** @var array<Role> */
    public array $Role = [
        Role::EDITOR,
        Role::VIEWER
    ];

    public function mount(): void
    {
        $this->team = Team::currentTeam();
        $this->authorize('addTeamMember', $this->team);
    }

    public function inviteMember(): void
    {
        $this->authorize('addTeamMember', $this->team);

        $this->validate();

        throw_if($this->email == Auth::user()->email, RuntimeException::class, __('You cannot invite yourself.'));

        $this->team->inviteUser($this->email, $this->roleChoosen);

        $this->reset('email', 'roleChoosen');
        $this->alert(__('Invitation sent successfully.'));
        $this->dispatch('members::invitation-sent');
    }

    public function removeMember(User $user): void
    {
        $this->authorize('removeTeamMember', Team::currentTeam());

       $this->team->deleteUser($user);

        $this->alert(__('Team member removed successfully.'));
        $this->dispatch('members::member-removed');
    }

    public function updateRole(User $user, Role $role): void
    {
        $this->authorize('updateTeamMember', Team::currentTeam());

        $this->team->updateUser($user, $role);

        $this->alert(__('Member role updated successfully.'));
        $this->dispatch('members::role-updated');
    }

    #[Computed]
    public function invitations(): Collection
    {
        return Invitation::query()
            ->where('team_id', $this->team->id)
            ->whereNull('cancelled_at')
            ->whereNull('accepted_at')
            ->get();
    }

    public function resendInvitation(int $invitationId): void
    {
        $invitation = Invitation::where('team_id', $this->team->id)
            ->where('id', $invitationId)
            ->first();

        throw_if(is_null($invitation), RuntimeException::class, __('Invitation not found.'));

        $invitation->resend();

        $this->alert(__('Invitation sent successfully.'));
        $this->dispatch('members::invitation-sent');
    }

    public function cancelInvitation(int $invitationId): void
    {
        $invitation = Invitation::where('team_id', $this->team->id)
            ->where('id', $invitationId)
            ->first();

        throw_if(is_null($invitation), RuntimeException::class, __('Invitation not found.'));

        $invitation->cancel();

        $this->alert(__('Invitation cancelled successfully.'));
        $this->dispatch('members::invitation-cancelled');

    }

    public function exception(Throwable $e, $stopPropagation): void
    {
        if ($e instanceof RuntimeException) {
            $stopPropagation();
            $this->alert(message: $e->getMessage(), type: 'error');
        }
    }

}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Team Members')" :subheading="__('All of the people that are part of this team.')">

        <!-- Add Member -->
        <div class="my-6">
            <form wire:submit="inviteMember" class="space-y-4">
                <flux:input
                    wire:model="email"
                    :label="__('Email Address')"
                    type="email"
                    required
                    :placeholder="__('member@example.com')"
                />

                <div>
                    <flux:label>{{ __('Role') }}</flux:label>
                    <div class="mt-2 space-y-2">
                        <label
                                @class([
                                    'flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors',
                                    'border-blue-500 bg-blue-50 dark:bg-blue-950/20' => $roleChoosen == $editorRole,
                                    'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700' => $roleChoosen != $editorRole,
                                ])
                            >
                            <input
                                type="radio"
                                wire:model.live="roleChoosen"
                                value="{{ $editorRole }}"
                                class="mt-1"
                                required
                            />
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ $editorRole->name() }}</div>
                                <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $editorRole->description() }}
                                </div>
                            </div>
                        </label>

                        <label
                                @class([
                                    'flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors',
                                    'border-blue-500 bg-blue-50 dark:bg-blue-950/20' => $roleChoosen == $viewerRole,
                                    'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700' => $roleChoosen != $viewerRole,
                                ])
                            >
                            <input
                                type="radio"
                                wire:model.live="roleChoosen"
                                value="{{ $viewerRole }}"
                                class="mt-1"
                            />
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ $viewerRole->name() }}</div>
                                <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $viewerRole->description() }}
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <flux:button variant="primary" type="submit">
                    {{ __('Invite Member') }}
                </flux:button>
            </form>
        </div>

        <flux:separator class="my-6" />

        <!-- Members -->
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

                @foreach($team->members()->get() as $member)

                    @php($currentRole = $member->membership->role)

                    <div class="flex items-center justify-between p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-3">
                            <flux:avatar
                                size="sm"
                                :initials="$member->initials()"
                            />
                            <div>
                                <div class="font-medium text-sm">
                                    {{ $member->name }}
                                    @if($member->id === $team->user_id)
                                        <flux:badge color="blue" size="sm" class="ml-2">{{ __('Owner') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $member->email }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <flux:select
                                wire:change="updateRole({{ $member->id }}, $event.target.value)"
                                size="sm"
                                class="w-34"
                            >
                                @foreach($Role as $role)
                                    <option
                                        value="{{ $role }}"
                                        @selected($currentRole === $role)
                                    >
                                        {{ $role->name() }}
                                    </option>
                                @endforeach
                            </flux:select>

                            <flux:button
                                wire:click="removeMember({{ $member->id }})"
                                wire:confirm="{{ __('Are you sure you want to remove this member?') }}"
                                variant="danger"
                                size="sm"
                            >
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    </div>
                @endforeach

                @if(!$this->invitations->isEmpty())
                    <flux:heading size="lg">{{ __('Pending Invitations') }}</flux:heading>

                    <div class="space-y-3">
                        @foreach($this->invitations as $invitation)
                            <div class="flex items-center justify-between p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon.envelope class="size-5 text-zinc-500" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm">{{ $invitation->email }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Role') }}: {{ $invitation->role->name() ?? $invitation->role->code() }}
                                            <span class="mx-1">•</span>
                                            {{ __('Sent') }} {{ $invitation->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <flux:button
                                        wire:click="resendInvitation({{ $invitation->id }})"
                                        variant="ghost"
                                        size="sm"
                                    >
                                        {{ __('Resend') }}
                                    </flux:button>

                                    <flux:button
                                        wire:click="cancelInvitation({{ $invitation->id }})"
                                        wire:confirm="{{ __('Are you sure you want to cancel this invitation?') }}"
                                        variant="danger"
                                        size="sm"
                                    >
                                        {{ __('Cancel') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-settings.layout>
</section>
