<?php

use App\Actions\Team\CreateNewTeam;
use App\Actions\Team\DeleteTeam;
use App\Actions\Team\RenameTeam;
use App\Livewire\BaseComponent;
use App\Models\Team\Team;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

new class extends BaseComponent {
    public string $createName = '';

    public ?int $renamingId = null;
    public string $renameName = '';

    public ?int $deletingId = null;
    public string $confirmName = '';

    /**
     * Workspaces owned by the local user, with diagram counts.
     */
    #[Computed]
    public function workspaces()
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->ownedTeams()->withCount('diagrams')->orderBy('name')->get();
    }

    #[Computed]
    public function currentId(): ?int
    {
        return Team::currentTeam()?->getKey();
    }

    #[Computed]
    public function deletingName(): string
    {
        return (string) ($this->workspaces->firstWhere('id', $this->deletingId)?->name ?? '');
    }

    public function create(CreateNewTeam $action): void
    {
        $validated = $this->validate([
            'createName' => ['required', 'string', 'max:255'],
        ]);

        $team = $action->exec([
            'name' => $validated['createName'],
            'user_id' => Auth::id(),
        ]);

        session(['current_team' => $team]);
        $this->createName = '';

        $this->dispatch('alert', message: __('Workspace created successfully.'));
    }

    public function startRename(int $id): void
    {
        $team = $this->ownedTeam($id);
        $this->authorize('update', $team);

        $this->resetErrorBag();
        $this->renamingId = $team->id;
        $this->renameName = $team->name;
    }

    public function cancelRename(): void
    {
        $this->reset('renamingId', 'renameName');
        $this->resetErrorBag();
    }

    public function rename(RenameTeam $action): void
    {
        $team = $this->ownedTeam((int) $this->renamingId);
        $this->authorize('update', $team);

        $this->validate(['renameName' => ['required', 'string', 'max:255']]);

        $renamed = $action->exec($team, $this->renameName);

        if ($this->currentId === $renamed->id) {
            session(['current_team' => $renamed]);
        }

        $this->cancelRename();
        $this->dispatch('alert', message: __('Workspace renamed successfully.'));
    }

    public function confirmDelete(int $id): void
    {
        $team = $this->ownedTeam($id);
        $this->authorize('delete', $team);

        $this->resetErrorBag();
        $this->deletingId = $team->id;
        $this->confirmName = '';

        $this->dispatch('workspace-delete-opened');
        Flux::modal('delete-workspace')->show();
    }

    public function delete(DeleteTeam $action): void
    {
        $team = $this->ownedTeam((int) $this->deletingId);
        $this->authorize('delete', $team);

        if ($this->confirmName !== $team->name) {
            $this->addError('confirmName', __('The name does not match.'));

            return;
        }

        $wasCurrent = $this->currentId === $team->id;

        try {
            $action->exec($team);
        } catch (ValidationException $e) {
            $this->closeDeleteModal();

            throw $e;
        }

        if ($wasCurrent) {
            session(['current_team' => Auth::user()->firstTeam()]);
        }

        $this->closeDeleteModal();
        $this->dispatch('alert', message: __('Workspace deleted successfully.'));
    }

    public function makeCurrent(int $id): void
    {
        $team = $this->ownedTeam($id);
        $this->authorize('view', $team);

        session(['current_team' => $team]);

        $this->redirect(route('dashboard'), navigate: true);
    }

    private function closeDeleteModal(): void
    {
        $this->reset('deletingId', 'confirmName');
        unset($this->workspaces, $this->currentId, $this->deletingName);
        Flux::modal('delete-workspace')->close();
    }

    private function ownedTeam(int $id): Team
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->ownedTeams()->findOrFail($id);
    }
}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Workspaces')" :subheading="__('Manage workspaces')">
        <div class="space-y-6">
            @error('team')
                <flux:text class="text-red-500">{{ $message }}</flux:text>
            @enderror

            <form wire:submit="create" class="flex items-start gap-2">
                <div class="flex-1">
                    <flux:input
                        wire:model="createName"
                        :placeholder="__('Workspace name')"
                        :aria-label="__('Workspace name')"
                        type="text"
                        required
                    />
                </div>
                <flux:button variant="primary" type="submit" class="cursor-pointer">{{ __('Create workspace') }}</flux:button>
            </form>

            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach($this->workspaces as $workspace)
                    <li class="flex items-center gap-3 py-3" wire:key="workspace-{{ $workspace->id }}">
                        <div class="min-w-0 flex-1">
                            @if($renamingId === $workspace->id)
                                <form wire:submit="rename" class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <flux:input wire:model="renameName" type="text" required autofocus :aria-label="__('Workspace name')" />
                                    </div>
                                    <flux:button type="submit" variant="primary" size="sm" class="cursor-pointer">{{ __('Save') }}</flux:button>
                                    <flux:button type="button" wire:click="cancelRename" size="sm" variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                                </form>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="truncate font-medium">{{ $workspace->name }}</span>
                                    @if($this->currentId === $workspace->id)
                                        <flux:badge color="teal" size="sm">{{ __('Current') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ trans_choice(':count diagram|:count diagrams', $workspace->diagrams_count) }}
                                </div>
                            @endif
                        </div>

                        @if($renamingId !== $workspace->id)
                            @if($this->currentId !== $workspace->id)
                                <flux:button size="sm" variant="ghost" wire:click="makeCurrent({{ $workspace->id }})" class="cursor-pointer">{{ __('Make current') }}</flux:button>
                            @endif
                            <flux:button size="sm" variant="ghost" wire:click="startRename({{ $workspace->id }})" class="cursor-pointer">{{ __('Rename') }}</flux:button>
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $workspace->id }})" class="cursor-pointer" :aria-label="__('Delete workspace')" />
                        @endif
                    </li>
                @endforeach
            </ul>
            @error('renameName')
                <flux:text class="text-red-500">{{ $message }}</flux:text>
            @enderror
            @error('createName')
                <flux:text class="text-red-500">{{ $message }}</flux:text>
            @enderror
        </div>

        <flux:modal name="delete-workspace" class="min-w-[25rem]" x-data="{ typed: '' }" x-on:workspace-delete-opened.window="typed = ''">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete workspace') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('This will permanently delete the workspace and all of its diagrams.') }}</flux:text>
                </div>

                <flux:input
                    wire:model="confirmName"
                    x-model="typed"
                    :label="__('Type :name to confirm', ['name' => $this->deletingName])"
                    type="text"
                />
                @error('confirmName')
                    <flux:text class="text-red-500">{{ $message }}</flux:text>
                @enderror

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:button type="button" variant="ghost" class="cursor-pointer" x-on:click="$flux.modal('delete-workspace').close()">{{ __('Cancel') }}</flux:button>
                    <flux:button
                        type="button"
                        variant="danger"
                        icon="trash"
                        class="cursor-pointer"
                        wire:click="delete"
                        x-bind:disabled="typed !== @js($this->deletingName)"
                    >{{ __('Delete workspace') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    </x-settings.layout>
</section>
