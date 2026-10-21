<div class="max-w-2xl">
    <flux:heading size="xl" class="font-normal text-zinc-900 dark:text-zinc-50">{{ __('Support the project') }}</flux:heading>

    <flux:text class="mt-2">
        {{ __('CraftDB is an open source project, built and maintained in spare time. If it helps you, there are a few ways to give something back. Thank you!') }}
    </flux:text>

    @if ($links)
        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($links as $link)
                <flux:button :href="$link['url']" target="_blank" rel="noopener noreferrer" :icon="$link['icon']" :variant="$link['variant']">
                    @unless ($link['icon'])
                        <x-lucide-coffee class="size-4 me-2" />
                    @endunless
                    {{ $link['label'] }}
                </flux:button>
            @endforeach
        </div>
    @endif

    @if ($pixKey)
        <div class="mt-8" data-test="support-pix" x-data="{ copied: false }">
            <flux:heading size="lg">{{ __('Pix') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Copy the key below to send a Pix.') }}</flux:text>

            <div class="mt-3 flex items-center gap-2">
                <flux:input readonly value="{{ $pixKey }}" class="font-mono" />
                <flux:button
                    type="button"
                    icon="clipboard"
                    x-on:click="navigator.clipboard.writeText(@js($pixKey)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                >
                    <span x-show="!copied">{{ __('Copy') }}</span>
                    <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
                </flux:button>
            </div>
        </div>
    @endif

    @unless ($links || $pixKey)
        <flux:callout class="mt-6" icon="information-circle">
            {{ __('More ways to support are coming soon.') }}
        </flux:callout>
    @endunless
</div>
