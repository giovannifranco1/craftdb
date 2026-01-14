<div class="space-y-4 min-h-[26rem]">
    <div>
        <flux:label badge="Required">{{ __('Select the database type') }}</flux:label>
        <div class="grid grid-cols-3 gap-3 mt-3">
            @foreach ([
                'mysql' => 'MySQL',
                'postgresql' => 'PostgreSQL',
                'sqlite' => 'SQLite',
            ] as $key => $label)
                <label
                    wire:loading.class.delay.shorter="opacity-25"
                    wire:target="database"
                    @class([
                        'relative cursor-pointer rounded-xl border-1 px-4 py-5 flex flex-col items-center min-h-28 transition-all duration-150 text-center group',
                        'border-primary-300 ring-primary-200 dark:ring-primary-900 bg-primary-50/40 dark:bg-primary-950/60' => $database === $key,
                        'border-zinc-300 hover:border-zinc-400 dark:border-zinc-600 dark:hover:border-zinc-600 bg-white/90 dark:bg-zinc-900/50' => $database !== $key,
                    ])
                >
                    <input
                        type="radio"
                        wire:model.live="database"
                        value="{{ $key }}"
                        class="sr-only"
                    />

                    <flux:icon name="database.{{ $key }}" class="size-13" />
                    <span class="font-light absolute text-sm bottom-2 start-4">{{ __($label) }}</span>

                    <span class="absolute top-2 right-2">
                        @if ($database === $key)
                            <flux:icon.check-circle variant="solid" class="size-5" />
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <flux:label badge="Optional" class="pb-3">{{ __('Import Files') }}</flux:label>
        <div class="bg-[#fafbfc] dark:bg-zinc-900/50 min-h-[15rem] rounded-lg flex flex-col justify-center items-center gap-3 p-4 border border-zinc-200 dark:border-zinc-700">
            <p class="text-sm text-zinc-600 dark:text-zinc-400 text-center max-w-md">
                {{ __('Import files or framework migrations to automatically create your diagram structure.') }}
            </p>
            <flux:button
                @click="$flux.modal('diagram-import').show()"
                variant="primary"
                icon="arrow-up-tray"
                :disabled="!$database"
            >
                {{ __('Import Files') }}
            </flux:button>

            @if(($importForm->type || !empty($importForm->file)) && $importForm->hasImport)
                <div class="mt-3 w-full">
                    @if($importForm->type === 'migration')
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Laravel Migration') }}</span>
                            <flux:icon.framework.laravel class="size-3 text-[#FF2D20]" />
                        </div>
                    @endif
                    @if(!empty($importForm->file))
                        <div class="flex flex-col gap-1 mt-2 w-full max-h-[15rem] overflow-y-scroll">
                            @foreach($importForm->file as $key => $item)
                                <div class="flex items-center gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                                    <flux:icon.check-circle color="#15B8A5" class="size-3" />
                                    <span class="text-[#15B8A5] text-xs font-normal font-[Space_Mono]">{{ $item->getClientOriginalName() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @include('components.diagram._partials.create.import')
</div>
