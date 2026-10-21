<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            <flux:navlist.item :href="route('language.edit')" wire:navigate>{{ __('Language') }}</flux:navlist.item>
            <flux:navlist.item :href="route('integrations.edit')" wire:navigate>{{ __('Integrations') }}</flux:navlist.item>

            <flux:separator class="my-2" />
            <flux:navlist.item :href="route('workspaces.index')" wire:navigate>{{ __('Workspaces') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading size="lg" class="underline">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-2xl bg-white dark:bg-background-dark border border-zinc-200 dark:border-zinc-700 rounded-lg p-5">
            {{ $slot }}
        </div>
    </div>
</div>
