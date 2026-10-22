<flux:modal :dismissible="false" name="diagram-canvas-export" class="md:w-150 z-30">
    <div class="space-y-6 flex flex-col">
        <!-- Header -->
        <div class="space-y-3">
            <flux:heading size="lg">
                {{ __('Export Migration') }}
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('Export your diagram as migration files for your preferred framework.') }}
            </flux:text>
        </div>

        <!-- Framework Selection -->
        <div>
            <flux:label class="pb-3">{{ __('Framework') }}</flux:label>
            <div class="grid grid-cols-2 gap-3">
                <label
                    @class([
                        'relative cursor-pointer rounded-xl border-1 px-4 py-5 flex flex-col dark:bg-background-dark items-center min-h-24 transition-all duration-150 text-center group dark:hover:bg-background-dark',
                        'border-primary-300 ring-primary-200 dark:ring-primary-900 bg-primary-50/40 dark:bg-background-dark' => $framework === 'laravel',
                        'border-zinc-300 hover:border-zinc-400 dark:border-zinc-600 dark:hover:border-zinc-600 bg-white/90 dark:bg-background-dark' => $framework !== 'laravel',
                    ])
                >
                    <input
                        type="radio"
                        wire:model.live="framework"
                        value="laravel"
                        class="sr-only"
                    />
                    <flux:icon.framework.laravel class="size-10 mb-2 text-[#FF2D20]" />
                    <span class="font-light text-sm">{{ __('Laravel') }}</span>
                    <span class="absolute top-2 right-2">
                        @if ($framework === 'laravel')
                            <flux:icon.check-circle variant="solid" class="size-5" />
                        @endif
                    </span>
                </label>

                <div
                    class="relative rounded-xl border-1 px-4 py-5 flex flex-col items-center min-h-24 transition-all duration-150 text-center cursor-not-allowed opacity-50 border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-background-dark"
                >
                    <flux:icon.code-bracket class="size-10 mb-2 text-zinc-400" />
                    <span class="font-light text-sm text-zinc-400">{{ __('Coming Soon') }}</span>
                    <span class="absolute top-2 right-2">
                        <flux:icon.lock-closed variant="solid" class="size-5 text-zinc-400 dark:text-zinc-500" />
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer actions -->
        <div class="flex items-center gap-2">
            <flux:button wire:click="cancel()" class="cursor-pointer" variant="ghost" icon="x-mark">{{ __('Cancel') }}</flux:button>

            <flux:spacer />

            <flux:button
                class="cursor-pointer"
                variant="primary"
                icon="arrow-down-tray"
                wire:click="export"
            >
                {{ __('Export') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
