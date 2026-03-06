<?php

use App\Livewire\BaseComponent;
use Illuminate\Support\Facades\Auth;

new class extends BaseComponent {
    /**
     * Get plans from config (excluding free).
     */
    public function getPlansProperty(): array
    {
        $plans = config('billing.plans', []);
        return collect($plans)->filter(fn ($p) => ! empty($p['price_id']))->all();
    }

    /**
     * Whether the user has an active subscription.
     */
    public function getSubscribedProperty(): bool
    {
        return Auth::user()->subscribed(config('billing.subscription_name', 'default'));
    }

    /**
     * Redirect to Stripe Checkout for the given plan key.
     */
    public function subscribe(string $plan): void
    {
        $this->redirect(route('billing.checkout', ['plan' => $plan]), navigate: false);
    }

    /**
     * Redirect to Stripe Billing Portal to manage subscription.
     */
    public function manageSubscription(): void
    {
        $this->redirect(route('billing.portal'), navigate: false);
    }
}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Billing')" :subheading="__('Manage your subscription and billing')">
        <div class="w-full space-y-6">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/30 p-4">
                    <flux:text class="text-emerald-800 dark:text-emerald-200">{{ session('success') }}</flux:text>
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950/30 p-4">
                    <flux:text class="text-red-800 dark:text-red-200">{{ session('error') }}</flux:text>
                </div>
            @endif

            {{-- Current plan --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                <flux:heading size="lg">{{ __('Current plan') }}</flux:heading>
                <flux:subheading class="mt-1">
                    @if ($this->subscribed)
                        {{ __('You are on a paid plan.') }}
                        <flux:button variant="ghost" size="sm" class="mt-2" wire:click="manageSubscription">
                            {{ __('Manage subscription') }}
                        </flux:button>
                    @else
                        {{ __('You are currently on the Free plan.') }}
                    @endif
                </flux:subheading>
            </div>

            {{-- Plans --}}
            <flux:heading size="lg" class="mt-6">{{ __('Available plans') }}</flux:heading>
            <div class="grid gap-4 sm:grid-cols-2 mt-2">
                @foreach ($this->plans as $planKey => $plan)
                    @php
                        $isCurrent = $this->subscribed; // could refine to match current price
                    @endphp
                    <div class="flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-5 shadow-sm">
                        <flux:heading size="lg">{{ $plan['name'] }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $plan['description'] }}</flux:text>
                        <div class="mt-3 flex items-baseline gap-1">
                            <span class="text-2xl font-bold">{{ config('cashier.currency', 'usd') === 'usd' ? '$' : '' }}{{ number_format($plan['price'], 0) }}</span>
                            <span class="text-zinc-500 dark:text-zinc-400">/ {{ $plan['interval'] === 'year' ? __('year') : __('month') }}</span>
                        </div>
                        <ul class="mt-4 space-y-2 flex-1">
                            @foreach ($plan['features'] ?? [] as $feature)
                                <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <flux:icon.check-circle class="size-4 text-emerald-500 shrink-0" />
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-5">
                            @if ($this->subscribed)
                                <flux:button variant="ghost" size="sm" wire:click="manageSubscription" class="w-full cursor-pointer">
                                    {{ __('Manage') }}
                                </flux:button>
                            @else
                                <flux:button variant="primary" color="sky" wire:click="subscribe('{{ $planKey }}')" class="w-full cursor-pointer">
                                    {{ __('Subscribe') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if (empty($this->plans))
                <flux:text class="text-zinc-500">{{ __('No paid plans are configured. Add Stripe price IDs in config/billing.php and .env.') }}</flux:text>
            @endif
        </div>
    </x-settings.layout>
</section>
