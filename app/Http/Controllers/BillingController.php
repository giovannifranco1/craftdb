<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    /**
     * Redirect to Stripe Checkout for the given plan.
     */
    public function checkout(Request $request, string $plan): RedirectResponse
    {
        $plans = config('billing.plans', []);
        $planConfig = $plans[$plan] ?? null;

        if (! $planConfig || empty($planConfig['price_id'])) {
            return redirect()->route('billing.index')->with('error', __('Invalid plan.'));
        }

        $user = $request->user();
        $priceId = $planConfig['price_id'];

        return $user
            ->newSubscription(config('billing.subscription_name', 'default'), $priceId)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.index'),
            ]);
    }

    /**
     * Redirect to Stripe Customer Billing Portal.
     */
    public function portal(Request $request): RedirectResponse
    {
        return $request->user()->redirectToBillingPortal(route('billing.index'));
    }

    /**
     * Success page after checkout (optional; can redirect to billing).
     */
    public function success(Request $request): RedirectResponse|View
    {
        return redirect()->route('billing.index')->with('success', __('Thank you! Your subscription is now active.'));
    }
}
