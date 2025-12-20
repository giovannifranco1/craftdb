<flux:modal :dismissible="false" name="diagram-import" class="md:w-150">
    <div class="space-y-6 flex flex-col">
        <!-- Header -->
        <div class="space-y-3">
            <flux:heading size="lg">
                {{ __('Import Source') }}
            </flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('Import a SQL file or Laravel migration to criar automaticamente a estrutura do seu diagrama.') }}
            </flux:text>
        </div>

        <!-- Import Type Selection -->
        <div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <label
                    wire:loading.class.delay.shorter="opacity-25"
                    wire:target="importForm.type"
                    @class([
                        'relative cursor-pointer rounded-xl border-1 px-4 py-5 flex flex-col items-center min-h-24 transition-all duration-150 text-center group',
                        'border-primary-300 ring-primary-200 dark:ring-primary-900 bg-primary-50/40 dark:bg-primary-950/60' => $importForm->type === 'file',
                        'border-zinc-300 hover:border-zinc-400 dark:border-zinc-600 dark:hover:border-zinc-600 bg-white/90 dark:bg-zinc-900/50' => $importForm->type !== 'file',
                    ])
                >
                    <input
                        type="radio"
                        wire:model.live="importForm.type"
                        value="sql"
                        class="sr-only"
                    />
                    <flux:icon.circle-stack class="size-10 text-zinc-600 mb-2" />
                    <span class="font-light text-sm">{{ __('SQL File') }}</span>
                    <span class="absolute top-2 right-2">
                        @if ($importForm->type === 'sql')
                            <flux:icon.check-circle variant="solid" class="size-5" />
                        @endif
                    </span>
                </label>

                <label
                    wire:loading.class.delay.shorter="opacity-25"
                    wire:target="importForm.type"
                    @class([
                        'relative cursor-pointer rounded-xl border-1 px-4 py-5 flex flex-col items-center min-h-24 transition-all duration-150 text-center group',
                        'border-primary-300 ring-primary-200 dark:ring-primary-900 bg-primary-50/40 dark:bg-primary-950/60' => $importForm->type === 'migration',
                        'border-zinc-300 hover:border-zinc-400 dark:border-zinc-600 dark:hover:border-zinc-600 bg-white/90 dark:bg-zinc-900/50' => $importForm->type !== 'migration',
                    ])
                >
                    <input
                        type="radio"
                        wire:model.live="importForm.type"
                        value="migration"
                        class="sr-only"
                    />
                    <flux:icon.framework.laravel class="size-10 mb-2 text-[#FF2D20]" />
                    <span class="font-light text-sm">{{ __('Laravel Migration') }}</span>
                    <span class="absolute top-2 right-2">
                        @if ($importForm->type === 'migration')
                            <flux:icon.check-circle variant="solid" class="size-5" />
                        @endif
                    </span>
                </label>
            </div>

            <div>
                <flux:label badge="Required" class="pb-3">{{ __('Import Files') }}</flux:label>
                <div class="bg-[#fafbfc] dark:bg-zinc-900/50 min-h-[15rem] rounded-lg flex flex-col justify-center items-center gap-3 p-4 border border-zinc-200 dark:border-zinc-700">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 text-center max-w-md">
                        @if ($importForm->type === 'migration')
                            {!! __('Import <span class="font-bold">.zip</span> or <span class="font-bold">.php</span> files to automatically create your diagram structure.') !!}
                        @else
                            {!! __('Import <span class="font-bold">.sql</span> files to automatically create your diagram structure.') !!}
                        @endif
                    </p>
                    <input
                        multiple
                        x-ref="file"
                        class="sr-only"
                        wire:model="importForm.file"
                        type="file"
                        accept="{{ $importForm->type === 'migration' ? '.php, .zip' : '.sql' }}"
                    >

                    @empty ($importForm->file)
                        <flux:button :disabled="!$importForm->type" @click="$refs.file.click()" variant="primary" icon="arrow-up-tray">{{ __('Select File') }}</flux:button>
                    @else
                        <flux:button wire:click="cancelFileImport()" class="cursor-pointer" vicon="x-mark">{{ __('Cancel') }}</flux:button>
                    @endempty

                    <div class="flex flex-col items-start w-full max-h-[15rem] overflow-y-scroll">
                        @forelse($importForm->file as $key => $item)
                            <div class="gap-2 flex items-center mr-4 mt-2">
                                <flux:icon.check-circle color="#15B8A5" class="size-4" />
                                <span class="text-[#15B8A5] text-xs font-normal font-[Space_Mono]">{{ $item->getClientOriginalName() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer actions -->
        <div class="flex items-center gap-2">
            <flux:button wire:click="cancelImport()" class="cursor-pointer" variant="ghost" icon="x-mark">{{ __('Cancel') }}</flux:button>

            <flux:spacer />

            <flux:button
                class="cursor-pointer"
                variant="primary"
                icon="check"
                wire:click="confirmImport()"
                :disabled="(is_null($importForm->type) || empty($importForm->file))"
            >
                {{ __('Confirm') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

