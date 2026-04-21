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
            'comparison' => $this->comparisonMatrix(),
            'packageEmbeds' => $packageEmbeds,
            'primaryActionUrl' => $primaryCta['url'],
            'primaryActionLabel' => $primaryCta['label'],
            'meta' => [
                'title' => 'Pricing | OmniReferral Lead Packages and Virtual Assistant Plans',
                'description' => 'Compare Starter, Growth, and Elite lead packages plus virtual assistant services built for busy real estate professionals.',
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
                'title' => 'Starter Lead',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/q61dioT6A8taz0yLfK93',
                'description' => 'Starter lead onboarding form for initial package setup and campaign handoff.',
            ],
            'power-leads' => [
                'title' => 'Growth Lead',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/ENBVclSqwUuX7awfOEM8',
                'description' => 'Growth lead onboarding form for teams scaling lead intake and routing.',
            ],
            'prime-leads' => [
                'title' => 'Elite Lead',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/z2wUhJG00x4n3MxY616R',
                'description' => 'Elite lead onboarding form for premium, high-intent package workflows.',
            ],
            'va-starter' => [
                'title' => 'ISA Support',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/DAYWVBJkNiVLEfoW740d',
                'description' => 'ISA support onboarding form for outreach and qualification operations.',
            ],
            'va-growth' => [
                'title' => 'Full Social Media Package',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/NiEcLMPWI084aKiAaNsi',
                'description' => 'Full social media package onboarding form for monthly content and growth execution.',
            ],
        ];
    }

    private function comparisonMatrix(): array
    {
        return [
            'headers' => [
                'Starter ($399/mo)',
                'Growth ($899/mo)',
                'Elite ($1,999/mo)',
            ],
            'rows' => [
                ['type' => 'group', 'label' => 'Lead Flow & Outreach'],
                ['feature' => 'Qualified Referrals', 'values' => ['✔', '✔✔', '✔✔✔']],
                ['feature' => 'AI + Human Outreach', 'values' => ['✔', '✔', '✔']],
                ['feature' => 'Cold Calling ISA', 'values' => ['❌', '1 ISA', '2 ISAs']],
                ['feature' => 'Territory Coverage', 'values' => ['2 Areas', '5 Areas', '10 Areas']],

                ['type' => 'group', 'label' => 'Deals & Revenue'],
                ['feature' => 'Wholesaler Access', 'values' => ['❌', '✔', '✔✔ Senior']],
                ['feature' => 'JV Deal Opportunities', 'values' => ['❌', '✔', '✔✔ Advanced']],
                ['feature' => 'Referral Fee', 'values' => ['15%', '7%', '5%']],
                ['feature' => 'Live Call Transfers', 'values' => ['❌', '❌', '✔']],
                ['feature' => 'Investor Access', 'values' => ['❌', '✔', '✔✔']],

                ['type' => 'group', 'label' => 'Platform & Operations'],
                ['feature' => 'Listings on Platform', 'values' => ['2', 'Up to 15', 'Unlimited']],
                ['feature' => 'Featured Listings', 'values' => ['❌', '✔', '✔✔ Priority']],
                ['feature' => 'CRM (GHL) Access', 'values' => ['❌', '❌', '✔ Full System']],
                ['feature' => 'Virtual Assistant', 'values' => ['❌', '❌', '✔ Full-Time']],

                ['type' => 'group', 'label' => 'Support & Performance'],
                ['feature' => 'Account Manager', 'values' => ['✔', 'Senior', 'Senior Team + VA']],
                ['feature' => 'Support Level', 'values' => ['Email', 'Priority', 'VIP']],
                ['feature' => 'Strategy Calls', 'values' => ['Monthly', 'Weekly', 'Weekly + Monthly Planning']],
                ['feature' => 'Performance Tracking', 'values' => ['Basic', 'Advanced', 'Dashboard + Forecasting']],
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
