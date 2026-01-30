<?php

use App\Livewire\BaseComponent;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Validate;

new class extends BaseComponent {

    #[Validate('required|string|in:en,pt_BR')]
    public string $locale = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->locale = App::getLocale();
    }

    /**
     * Update the language preference.
     */
    public function updateLanguage(): void
    {
       $validated = $this->validate();

        Auth::user()->update(['locale' => $validated['locale']]);

        session()->put('locale', $validated['locale']);
        app()->setLocale($validated['locale']);

        $this->alert(message: __('Language updated successfully.'), type: 'success');
        $this->redirect(route('language.edit'), navigate: true);
    }
}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Language')" :subheading="__('Update the language settings for your account')">
        <form wire:submit="updateLanguage">
            <flux:radio.group wire:model="locale" variant="segmented">
                <flux:radio value="pt_BR">{{ __('Portuguese (Brazil)') }}</flux:radio>
                <flux:radio value="en">{{ __('English') }}</flux:radio>
            </flux:radio.group>

            <div class="mt-4">
                <flux:button type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
