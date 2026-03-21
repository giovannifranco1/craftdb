<?php

use App\Livewire\BaseComponent;
use Illuminate\Support\Facades\Auth;

new class extends BaseComponent {
    public function updateAppearance(bool $isDark): void
    {
        Auth::user()->update(['is_dark' => $isDark]);
    }
}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance"
            @change="$wire.updateAppearance($flux.appearance === 'dark')">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
