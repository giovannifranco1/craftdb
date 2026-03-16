<flux:modal name="pro-feature" class="md:w-[32rem] !p-0 overflow-hidden">
    <div x-data x-show="$store.proFeature">

        <!-- Hero -->
        <div class="relative flex flex-col items-center justify-center gap-3 px-8 py-10 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 text-white overflow-hidden">
            <!-- decorative blobs -->
            <div class="absolute -top-10 -left-10 w-48 h-48 rounded-full bg-violet-500/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -right-10 w-48 h-48 rounded-full bg-amber-400/20 blur-3xl pointer-events-none"></div>

            <!-- icon -->
            <div class="relative z-10 flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 ring-1 ring-white/20 shadow-lg">
                <x-lucide-rocket class="w-8 h-8 text-amber-300" />
            </div>

            <div class="relative z-10 text-center space-y-1">
                <h2 class="text-xl font-bold tracking-tight">
                    <span x-text="$store.proFeature?.name || '{{ __('Upgrade to Pro') }}'"></span>
                </h2>
                <p class="text-sm text-zinc-400" x-show="$store.proFeature?.description">
                    <span x-text="$store.proFeature?.description"></span>
                </p>
            </div>
        </div>

        <!-- Body -->
        <div class="px-8 py-6 space-y-5">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('This feature is available on the Pro plan or higher. Upgrade to unlock it and get unlimited diagrams, tables, and more.') }}
            </p>

            <!-- Feature highlights -->
            <ul class="space-y-2">
                @foreach([
                    ['icon' => 'infinity',        'text' => __('Diagramas ilimitados')],
                    ['icon' => 'table',            'text' => __('Import SQL / Laravel Migrations')],
                    ['icon' => 'sticky-note',      'text' => __('Áreas, Sticky Notes, Graph View')],
                    ['icon' => 'zap',              'text' => __('Suporte prioritário')],
                ] as $item)
                <li class="flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex-shrink-0">
                        <x-dynamic-component :component="'lucide-' . $item['icon']" class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                    </span>
                    {{ $item['text'] }}
                </li>
                @endforeach
            </ul>

            <!-- Actions -->
            <div class="flex items-center gap-2 pt-1">
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('pro-feature').close()" class="cursor-pointer">
                    {{ __('Maybe later') }}
                </flux:button>
                <flux:spacer />
                <flux:button href="{{ route('billing.index') }}" variant="primary" icon="sparkles" wire:navigate class="cursor-pointer">
                    {{ __('Upgrade to Pro') }}
                </flux:button>
            </div>
        </div>

    </div>
</flux:modal>
