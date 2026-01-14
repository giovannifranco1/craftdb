<div class="space-y-4 min-h-[26rem]">
    <!-- Warning Alert -->
    @if($showWarning)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Warning: Data Loss') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Changing the style or database type will delete all tables, areas, and notes from this diagram. This action cannot be undone.') }}
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="space-y-2">
        <flux:label class="pb-2">{{ __('Style') }}</flux:label>

        <div class="flex flex-col gap-4 grow pt-2 pb-3">
            <label
                @class([
                    'relative border rounded-xl p-6 cursor-pointer dark:hover:border-primary-400 dark:hover:bg-primary-950/50 dark:bg-primary-900/50 hover:border-primary-300 hover:bg-primary-50/60 transition flex flex-col',
                    'border-primary-400 dark:border-primary-500 dark:hover:bg-primary-950/50 bg-primary-50 dark:bg-primary-950/60' => $style === 'colorful',
                    'border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600 dark:bg-zinc-900/50' => $style !== 'colorful',
                ])
            >
                <input
                    type="radio"
                    class="sr-only"
                    wire:model.live="style"
                    value="colorful">
                <div class="flex items-center gap-3 mb-1">
                    <flux:icon.swatch variant="solid" class="size-4" />
                    <span class="text-sm font-semibold">{{ __('Colorful') }}</span>
                </div>
                <p class="text-sm text-zinc-600 leading-relaxed min-h-[55px] max-h-[55px]">
                    {{ __('Displays the diagram with distinct colors for each table and relationship, making it easier to distinguish and focus on specific areas of your model.') }}
                </p>

                <span class="absolute top-2 right-2">
                    @if ($style === 'colorful')
                        <flux:icon.check-circle variant="solid" class="size-5" />
                    @endif
                </span>
            </label>

            <label
                @class([
                    'relative border rounded-xl p-6 cursor-pointer hover:border-primary-300 hover:bg-primary-50/60 dark:hover:bg-primary-950/50 transition flex flex-col',
                    'border-primary-400 dark:border-primary-500 dark:hover:bg-primary-950/50 bg-primary-50 dark:bg-primary-950/60' => $style === 'neutral',
                    'border-zinc-300 dark:border-zinc-700 dark:hover:border-zinc-600 dark:bg-zinc-900/50' => $style !== 'neutral',
                ])
            >
                <input
                    type="radio"
                    class="sr-only"
                    wire:model.live="style"
                    value="neutral">
                <div class="flex items-center gap-3 mb-1">
                    <flux:icon.swatch variant="solid" class="size-4" />
                    <span class="text-sm font-semibold">{{ __('Neutral') }}</span>
                </div>
                <p class="text-sm text-zinc-600 leading-relaxed min-h-[55px] max-h-[55px]">
                    {{ __('Displays the diagram with a single neutral color for a cleaner, distraction-free look.') }}
                </p>

                <span class="absolute top-2 right-2">
                    @if ($style === 'neutral')
                        <flux:icon.check-circle variant="solid" class="size-5" />
                    @endif
                </span>
            </label>
        </div>
    </div>

    <div>
        <flux:label badge="Required">{{ __('Database Type') }}</flux:label>
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
</div>
