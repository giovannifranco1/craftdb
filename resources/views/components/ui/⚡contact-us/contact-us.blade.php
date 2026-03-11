<flux:modal name="contact-us" class="md:w-[28rem]">
    <form wire:submit="submit()">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Contact Us') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    {{ __('Need help, have feedback, an idea, or a request? Share it with us below.') }}
                </flux:text>
            </div>

            <flux:field>
                <flux:label>{{ __('Your message') }}</flux:label>
                <flux:textarea
                    wire:model="message"
                    rows="5"
                    placeholder="{{ __('Tell us what\'s on your mind...') }}"
                    class="mt-1"
                />
                <flux:error name="message" />
            </flux:field>
        </div>

        <div class="flex gap-2 mt-6">
            <flux:button type="button" variant="ghost" icon="x-mark" wire:click="cancel()" class="cursor-pointer">
                {{ __('Cancel') }}
            </flux:button>
            <flux:spacer />
            <flux:button type="submit" variant="primary" icon="paper-airplane" class="cursor-pointer">
                {{ __('Send') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
