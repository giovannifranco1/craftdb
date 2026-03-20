<?php

use App\Enums\BillingCountry;
use App\Livewire\BaseComponent;
use Illuminate\Support\Facades\Auth;

new class extends BaseComponent {
    public bool $annual = false;

    /**
     * Billing country from CF-IPCountry header (US or BR, default US).
     */
    public function getBillingCountryProperty(): BillingCountry
    {
        return BillingCountry::fromRequest(request()->header('CF-IPCountry'));
    }

    /**
     * Plans available for the user's country, filtered by billing interval.
     */
    public function getPlansProperty(): array
    {
        $plans = config('billing.plans', []);
        $country = $this->billingCountry;
        $planKeys = $country->planKeys();
        $interval = $this->annual ? 'year' : 'month';

        return collect($plans)
            ->filter(fn ($p, $key) => in_array($key, $planKeys, true) && ! empty($p['price_id']) && $p['interval'] === $interval)
            ->all();
    }

    /**
     * Whether the user has an active subscription.
     */
    public function getSubscribedProperty(): bool
    {
        return Auth::user()->subscribed(config('billing.subscription_name', 'default'));
    }

    /**
     * Current plan config (name, interval, price) from subscription, or null if free.
     */
    public function getCurrentPlanProperty(): ?array
    {
        $user = Auth::user();

        if (! $user->subscribed(config('billing.subscription_name', 'default'))) {
            return null;
        }

        $sub = $user->subscription(config('billing.subscription_name', 'default'));
        $priceId = $sub->stripe_price ?? $sub->items->first()?->stripe_price ?? null;

        if (! $priceId) {
            return null;
        }

        foreach (config('billing.plans', []) as $key => $plan) {
            if (isset($plan['price_id']) && $plan['price_id'] === $priceId) {
                return [
                    'key' => $key,
                    'name' => $plan['name'] ?? 'Pro',
                    'interval' => $plan['interval'] ?? 'month',
                    'price' => $plan['price'] ?? 0,
                ];
            }
        }

        return ['key' => null, 'name' => __('Pro'), 'interval' => 'month', 'price' => 0];
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
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">{{ __('Current plan') }}</flux:heading>
                        @if ($this->currentPlan)
                            @if(str_contains($this->currentPlan['key'] ?? '', 'team'))
                                <flux:badge color="violet" size="sm">{{ $this->currentPlan['name'] }}</flux:badge>
                            @else
                                <flux:badge color="lime" size="sm">{{ $this->currentPlan['name'] }}</flux:badge>
                            @endif
                        @else
                            <flux:badge color="sky" size="sm">{{ config('billing.plans.free.name', 'Free') }}</flux:badge>
                        @endif
                    </div>
                    @if ($this->subscribed)
                        <flux:button class="cursor-pointer" variant="ghost" size="sm" wire:click="manageSubscription">
                            {{ __('Manage subscription') }}
                        </flux:button>
                    @endif
                </div>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    @if ($this->currentPlan)
                        {{ $this->currentPlan['interval'] === 'year' ? __('Billed annually') : __('Billed monthly') }}
                        @if (($this->currentPlan['price'] ?? 0) > 0)
                            · {{ $this->billingCountry->currencySymbol() }}{{ number_format($this->currentPlan['price'], 2) }}/{{ $this->currentPlan['interval'] === 'year' ? __('year') : __('month') }}
                        @endif
                    @else
                        {{ __('Free plan for personal projects and learning.') }}
                    @endif
                </flux:text>
            </div>

            {{-- Plans --}}
            <div class="flex items-center justify-between mt-6">
                <flux:heading size="lg">{{ __('Available plans') }}</flux:heading>
                <div class="flex items-center gap-3">
                    <flux:text size="sm" :class="! $annual ? 'font-medium text-zinc-900 dark:text-zinc-100' : 'text-zinc-500'">{{ __('Monthly') }}</flux:text>
                    <flux:switch class="cursor-pointer" wire:model.live="annual" />
                    <flux:text size="sm" :class="$annual ? 'font-medium text-zinc-900 dark:text-zinc-100' : 'text-zinc-500'">
                        {{ __('Annual') }}
                        <span class="ml-1 inline-block px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-medium">~17% off</span>
                    </flux:text>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 mt-2">
                @foreach ($this->plans as $planKey => $plan)
                    @php
                        $isCurrent = $this->subscribed; // could refine to match current price
                    @endphp
                    <div class="flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-5 shadow-sm">
                        <flux:heading size="lg">{{ $plan['emoji'] ?? '' }} {{ $plan['name'] }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">{{ __($plan['description']) }}</flux:text>
                        <div class="mt-3 flex items-baseline gap-1">
                            <span class="text-2xl font-bold">{{ $this->billingCountry->currencySymbol() }}{{ number_format($plan['price'], 2) }}</span>
                            <span class="text-zinc-500 dark:text-zinc-400">/ {{ $plan['interval'] === 'year' ? __('year') : __('month') }}</span>
                        </div>
                        <ul class="mt-4 space-y-2 flex-1">
                            @foreach ($plan['features'] ?? [] as $feature)
                                <li class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                    <flux:icon.check-circle class="size-4 text-emerald-500 shrink-0" />
                                    {{ __($feature) }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-5">
                            @if ($this->subscribed)
                                <flux:button variant="ghost" size="sm" wire:click="manageSubscription" class="w-full cursor-pointer">
                                    {{ __('Manage in Stripe') }}
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
