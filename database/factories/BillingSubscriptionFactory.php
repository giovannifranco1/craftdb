<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Laravel\Cashier\Subscription;

/**
 * Fake Cashier subscriptions locally / in tests without calling Stripe.
 *
 * Tinker examples:
 *
 *   use App\Models\User;
 *   use Database\Factories\BillingSubscriptionFactory;
 *
 *   $u = User::where('email', 'you@example.com')->first();
 *   BillingSubscriptionFactory::grantPro($u);
 *
 *   // Custom price id (must match config used by your feature checks):
 *   BillingSubscriptionFactory::grant($u, config('billing.plans.pro_brl.price_id'));
 */
final class BillingSubscriptionFactory
{
    public static function grantPro(User $user, ?string $stripePriceId = null): Subscription
    {
        $priceId = $stripePriceId ?? config('billing.plans.pro_usd.price_id');

        return self::grant($user, (string) $priceId);
    }

    public static function grantTeam(User $user, ?string $stripePriceId = null): Subscription
    {
        $priceId = $stripePriceId ?? config('billing.plans.team_usd.price_id');

        return self::grant($user, (string) $priceId);
    }

    public static function grant(User $user, string $stripePriceId): Subscription
    {
        $subscription = Subscription::factory()
            ->for($user)
            ->withPrice($stripePriceId)
            ->create([
                'type' => config('billing.subscription_name', 'default'),
            ]);

        User::clearSubscriptionCaches();

        return $subscription;
    }
}
