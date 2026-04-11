<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\StripeCheckoutService;
use App\Support\PricingContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $packageEmbeds = $this->packageEmbeds();
        $primaryCta = $this->primaryAction();

        return view('pages.pricing', [
            'leadPackages' => Package::active()->leadPlans()->orderBy('sort_order')->orderBy('one_time_price')->get(),
            'assistantPackages' => Package::active()->assistantPlans()->orderBy('sort_order')->orderBy('monthly_price')->get(),
            'pricingPlans' => PricingContent::plans(),
            'packageEmbeds' => $packageEmbeds,
            'primaryActionUrl' => $primaryCta['url'],
            'primaryActionLabel' => $primaryCta['label'],
            'meta' => [
                'title' => 'Pricing | OmniReferral Lead Packages and Virtual Assistant Plans',
                'description' => 'Compare Quick, Power, and Prime lead packages plus virtual assistant services built for busy real estate professionals.',
            ],
        ]);
    }

    public function checkout(Package $package): View
    {
        $pricingPlan = PricingContent::planBySlug($package->slug);
        $packageEmbed = $this->packageEmbeds()[$package->slug] ?? null;
        $postPurchaseAction = $this->postPurchaseAction();
        $packageDisplay = [
            'name' => $pricingPlan['name'] ?? $package->name,
            'tier' => $pricingPlan['tier'] ?? ($package->category === 'lead' ? 'Lead Package' : 'Virtual Assistance'),
            'summary' => $pricingPlan['summary'] ?? $package->description,
            'price_note' => $pricingPlan['price_note'] ?? ($package->one_time_price ? '/ One-Time' : '/ Monthly'),
            'features' => $pricingPlan['features'] ?? ($package->features ?? []),
            'value_price' => $pricingPlan['value_price'] ?? null,
        ];
        $packageEmbed = [
            'title' => $packageEmbed['title'] ?? $packageDisplay['name'],
            'src' => $packageEmbed['src'] ?? $package->ghl_form_url,
            'description' => $packageEmbed['description']
                ?? ($packageDisplay['summary'] ?: 'Complete the follow-up setup form after payment to help OmniReferral provision your workspace correctly.'),
        ];
        $billingOptions = collect([
            $package->one_time_price ? [
                'key' => 'one_time',
                'label' => 'Pay One-Time',
                'amount' => $package->one_time_price,
                'note' => 'Secure one-time checkout for the selected package.',
                'button' => 'button--orange',
            ] : null,
            $package->monthly_price ? [
                'key' => 'monthly',
                'label' => 'Subscribe Monthly',
                'amount' => $package->monthly_price,
                'note' => 'Recurring billing for ongoing access and support.',
                'button' => 'button--ghost-blue',
            ] : null,
        ])->filter()->values();

        return view('pages.package-checkout', [
            'package' => $package,
            'packageDisplay' => $packageDisplay,
            'packageEmbed' => $packageEmbed,
            'billingOptions' => $billingOptions,
            'stripeEnabled' => (bool) config('services.stripe.secret'),
            'postPurchaseActionUrl' => $postPurchaseAction['url'],
            'postPurchaseActionLabel' => $postPurchaseAction['label'],
            'meta' => [
                'title' => $packageDisplay['name'] . ' Checkout | OmniReferral',
                'description' => 'Continue to payment and post-purchase setup for the ' . $packageDisplay['name'] . ' package.',
            ],
        ]);
    }

    public function startCheckout(Request $request, Package $package, StripeCheckoutService $checkoutService): RedirectResponse
    {
        $validated = $request->validate([
            'billing' => ['nullable', 'in:auto,one_time,monthly'],
            'role' => ['nullable', 'in:buyer,seller,agent,admin,staff,guest'],
        ]);

        $session = $checkoutService->createPackageCheckout($package, Auth::user(), [
            'billing' => $validated['billing'] ?? 'auto',
            'role' => $validated['role'] ?? (Auth::user()?->role ?? 'guest'),
            'customer_email' => Auth::user()?->email,
            'success_url' => route('packages.success', $package) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('packages.checkout', $package),
        ]);

        if (! $session?->url) {
            return back()->with('error', 'Stripe checkout is not configured yet for this environment. Use the embedded GoHighLevel form or configure your Stripe keys first.');
        }

        return redirect()->away($session->url);
    }

    public function success(Package $package): View
    {
        $postPurchaseAction = $this->postPurchaseAction();

        return view('pages.package-success', [
            'package' => $package,
            'sessionId' => request()->string('session_id')->value(),
            'postPurchaseActionUrl' => $postPurchaseAction['url'],
            'postPurchaseActionLabel' => $postPurchaseAction['label'],
            'meta' => [
                'title' => 'Payment Success | OmniReferral',
                'description' => 'Welcome aboard! Your OmniReferral package is ready and your next access step is available.',
            ],
        ]);
    }

    private function packageEmbeds(): array
    {
        return [
            'quick-leads' => [
                'title' => 'Quick Package',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/6VrZG7vbNueWG6hoqYru',
                'description' => 'Start with a lighter lead package and let GoHighLevel handle the follow-up setup after the form is complete.',
            ],
            'power-leads' => [
                'title' => 'Power Package',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/NmuErgwOkT4c83tl1k12',
                'description' => 'Our most popular package for teams that want stronger qualification and momentum.',
            ],
            'prime-leads' => [
                'title' => 'Prime Package',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/DAnafQ8CfUsIMsj8Zq4D',
                'description' => 'Premium lead routing for agents who want the highest-intent opportunities.',
            ],
            'va-starter' => [
                'title' => 'Cold Calling Monthly',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/CV8WmfWmoDlJ5GEO9B99',
                'description' => 'A monthly support option for teams that want consistent outbound activity and follow-up help.',
            ],
            'va-growth' => [
                'title' => 'Social Media Monthly',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/ye7sDOoYsZaiCNjWRARI',
                'description' => 'A monthly campaign package for visibility, nurture, and stronger brand support.',
            ],
        ];
    }

    private function primaryAction(): array
    {
        $user = Auth::user();

        if ($user) {
            return [
                'url' => $user->dashboardRoute(),
                'label' => 'Open Dashboard',
            ];
        }

        return [
            'url' => route('register'),
            'label' => 'Start Today',
        ];
    }

    private function postPurchaseAction(): array
    {
        $user = Auth::user();

        if ($user) {
            return [
                'url' => $user->dashboardRoute(),
                'label' => 'Open Dashboard',
            ];
        }

        return [
            'url' => route('login'),
            'label' => 'Go To Login',
        ];
    }
}
