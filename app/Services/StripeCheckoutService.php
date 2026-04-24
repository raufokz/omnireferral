<?php

namespace App\Services;

use App\Models\Package;
use App\Models\User;
use App\Support\PricingContent;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

class StripeCheckoutService
{
    public function configured(): bool
    {
        return (bool) config('services.stripe.secret');
    }

    public function createPackageCheckout(Package $package, ?User $user = null, array $options = []): ?Session
    {
        if (! $this->configured()) {
            return null;
        }

        $billing = Arr::get($options, 'billing', 'auto');
        $mode = $package->preferredCheckoutMode($billing);
        $amount = $package->preferredCheckoutAmount($billing);

        if (! $amount) {
            return null;
        }

        $pricingPlan = PricingContent::planBySlug($package->slug);
        $productName = (string) ($pricingPlan['name'] ?? $package->name);
        $productDescription = (string) ($pricingPlan['summary'] ?? ($package->description ?: ''));
        $featureLines = (array) ($pricingPlan['features'] ?? ($package->features ?? []));
        if ($productDescription === '' && ! empty($featureLines)) {
            $productDescription = Str::limit(implode(', ', $featureLines), 150);
        }

        $stripe = new StripeClient(config('services.stripe.secret'));
        $lineItem = $package->stripe_price_id
            ? ['price' => $package->stripe_price_id, 'quantity' => 1]
            : [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $amount * 100,
                    'product_data' => [
                        'name' => $productName,
                        'description' => $productDescription,
                    ],
                    'recurring' => $mode === 'subscription' ? ['interval' => 'month'] : null,
                ],
                'quantity' => 1,
            ];

        if ($mode !== 'subscription') {
            unset($lineItem['price_data']['recurring']);
        }

        return $stripe->checkout->sessions->create([
            'mode' => $mode,
            'line_items' => [$lineItem],
            'success_url' => Arr::get($options, 'success_url'),
            'cancel_url' => Arr::get($options, 'cancel_url'),
            'customer' => $user?->stripe_customer_id ?: null,
            'customer_email' => $user && ! $user->stripe_customer_id ? $user->email : Arr::get($options, 'customer_email'),
            'metadata' => [
                'package_id' => (string) $package->id,
                'package_slug' => $package->slug,
                'billing' => $billing,
                'user_id' => (string) ($user?->id ?? 0),
                'role' => $user?->role ?? Arr::get($options, 'role', 'guest'),
            ],
            'client_reference_id' => (string) ($user?->id ?? 0),
            'allow_promotion_codes' => true,
        ]);
    }
}
