<?php

namespace App\Http\Controllers;

use App\Enums\BillingCountry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Cashier\Checkout;

class BillingController extends Controller
{
    /**
     * Redirect to Stripe Checkout for the given plan.
     * Uses CF-IPCountry header to resolve country-specific price (US or BR).
     */
    public function checkout(Request $request, string $plan): Checkout|RedirectResponse
    {
        $country = BillingCountry::fromRequest($request->header('CF-IPCountry'));
        $plans = config('billing.plans', []);

        if (! $country->hasPlan($plan)) {
            return redirect()->route('billing.index')->with('error', __('Invalid plan for your region.'));
        }

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
                'billing_address_collection' => 'required',
                // 'consent_collection' => ['terms_of_service' => 'required'], // requires Terms URL set in Stripe Dashboard > Settings > Public details
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
    public function success(): RedirectResponse|View
    {
        return redirect()->route('billing.index')->with('success', __('Thank you! Your subscription is now active.'));
    }
}
