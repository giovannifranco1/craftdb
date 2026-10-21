<div>
    @php($current = $this->currentTeam)

    <flux:menu.radio.group>
        <div class="px-2 py-1.5 text-xs font-semibold text-zinc-500 dark:text-zinc-400">
            {{ __('Workspace') }}
        </div>

        @if($current)
            <div class="flex items-center justify-between px-2 py-1.5 text-sm font-medium">
                <span class="truncate">{{ $current->name }}</span>
                <flux:icon.check-circle class="size-4 text-[#15B8A5]" />
            </div>
        @endif

        @foreach($this->getAllTeams->reject(fn ($team) => $team->id === $current?->id) as $team)
            <flux:menu.item
                wire:click="switchTeam({{ $team->id }})"
                class="cursor-pointer"
            >
                <span>{{ $team->name }}</span>
            </flux:menu.item>
        @endforeach

        <flux:menu.item :href="route('workspaces.index')" icon="squares-2x2" wire:navigate>
            {{ __('Manage workspaces') }}
        </flux:menu.item>
    </flux:menu.radio.group>

    <flux:menu.separator />
</div>
