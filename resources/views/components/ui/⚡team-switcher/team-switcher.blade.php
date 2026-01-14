<div>
    @use('App\Models\Team\Team')

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
                        @if(Team::currentTeam() && Team::currentTeam()->id === $team->id)
                            <flux:icon.check-circle class="size-4 text-[#15B8A5]" />
                        @endif
                    </div>
                </flux:menu.item>
            @endforeach
        </flux:menu.radio.group>

        <flux:menu.separator />
    @endif
</div>
