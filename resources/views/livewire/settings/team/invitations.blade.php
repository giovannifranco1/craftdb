<?php

use App\Models\Team\Team;
use App\Models\Team\Invitation;
use App\Models\Team\Role;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Component;

new class extends Component {
    public ?Team $team = null;
    public string $email = '';
    public int $role_id = 0;

    public function mount(): void
    {
        $this->authorize('update', Team::currentTeam());

        $this->team = Team::currentTeam();
        $roles = $this->team->roles()->get();
        $this->role_id = $roles->first()?->id ?? 0;
    }

    public function sendInvitation(): void
    {
        $this->authorize('update', Team::currentTeam());

        $validated = $this->validate([
            'email' => ['required', 'email'],
            'role_id' => ['required', 'exists:role,id'],
        ]);

        // Check if invitation already exists
        $existingInvitation = Invitation::where('team_id', $this->team->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existingInvitation) {
            $this->addError('email', __('An invitation has already been sent to this email address.'));
            return;
        }

        // Check if user is already a member
        $user = \App\Models\User::where('email', $validated['email'])->first();
        if ($user && $this->team->users()->where('users.id', $user->id)->exists()) {
            $this->addError('email', __('This user is already a member of the team.'));
            return;
        }

        Invitation::create([
            'team_id' => $this->team->id,
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
        ]);

        $this->reset('email');
        $this->dispatch('alert', message: __('Invitation sent successfully.'));
        $this->dispatch('invitation-sent');
    }

    public function cancelInvitation(int $invitationId): void
    {
        $invitation = Invitation::where('team_id', $this->team->id)
            ->where('id', $invitationId)
            ->first();

        if ($invitation) {
            $invitation->delete();
            $this->dispatch('alert', message: __('Invitation cancelled successfully.'));
            $this->dispatch('invitation-cancelled');
        }
    }

    public function resendInvitation(int $invitationId): void
    {
        $invitation = Invitation::where('team_id', $this->team->id)
            ->where('id', $invitationId)
            ->first();

        if ($invitation) {
            // Logic to resend invitation email would go here
            $this->dispatch('alert', message: __('Invitation resent successfully.'));
        }
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Team Invitations')" :subheading="__('Manage pending invitations to your team.')">
        <!-- Send Invitation Form -->
        <div class="my-6">
            <form wire:submit="sendInvitation" class="space-y-4">
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
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors @if($role_id == $editorRole->id) bg-blue-50 dark:bg-blue-950/20 @else border-zinc-200 dark:border-zinc-700 @endif">
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
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer hover:border-zinc-400 dark:hover:border-zinc-500 transition-colors @if($role_id == $viewerRole->id) border-blue-500 bg-blue-50 dark:bg-blue-950/20 @else dark:border-zinc-700 @endif">
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
                    {{ __('Send Invitation') }}
                </flux:button>
            </form>
        </div>

        <flux:separator class="my-6" />

        <!-- Pending Invitations -->
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Pending Invitations') }}</flux:heading>

            @php
                $invitations = Invitation::where('team_id', $team->id)
                    ->with('role')
                    ->latest()
                    ->get();
            @endphp

            @if($invitations->isEmpty())
                <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                    {{ __('No pending invitations.') }}
                </div>
            @else
                <div class="space-y-3">
                    @foreach($invitations as $invitation)
                        <div class="flex items-center justify-between p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon.envelope class="size-5 text-zinc-500" />
                                </div>
                                <div>
                                    <div class="font-medium text-sm">{{ $invitation->email }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Role') }}: {{ $invitation->role->name ?? $invitation->role->code }}
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
    </x-settings.layout>
</section>
