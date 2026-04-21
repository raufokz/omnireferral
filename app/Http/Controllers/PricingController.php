<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\StripeCheckoutService;
use App\Support\PackageComparison;
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
            'comparison' => PackageComparison::fromLeadPackages(
                Package::active()->leadPlans()->orderBy('sort_order')->orderBy('one_time_price')->get()
            ),
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

    public function success(Request $request, Package $package): View|RedirectResponse
    {
        $postPurchaseAction = $this->postPurchaseAction();
        $sessionId = $request->string('session_id')->value();
        $stripeSecret = (string) config('services.stripe.secret');

        if ($stripeSecret !== '') {
            if ($sessionId === '') {
                return redirect()
                    ->route('packages.checkout', $package)
                    ->with('error', 'Missing payment confirmation. Please complete checkout again from the pricing page.');
            }

            try {
                $stripe = new \Stripe\StripeClient($stripeSecret);
                $session = $stripe->checkout->sessions->retrieve($sessionId);
            } catch (\Stripe\Exception\ApiErrorException) {
                return redirect()
                    ->route('packages.checkout', $package)
                    ->with('error', 'We could not verify that payment session with Stripe. Please try again or contact support.');
            }

            $paid = in_array($session->payment_status ?? '', ['paid', 'no_payment_required'], true)
                || ($session->status ?? '') === 'complete';

            if (! $paid) {
                return redirect()
                    ->route('packages.checkout', $package)
                    ->with('error', 'That checkout session is not marked as paid yet.');
            }

            $sessionPackageId = (int) ($session->metadata->package_id ?? 0);
            if ($sessionPackageId !== (int) $package->id) {
                return redirect()
                    ->route('pricing')
                    ->with('error', 'The confirmed package does not match this success page.');
            }
        }

        return view('pages.package-success', [
            'package' => $package,
            'sessionId' => $sessionId,
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
                'title' => 'Starter',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/q61dioT6A8taz0yLfK93',
                'description' => 'Starter lead onboarding form for initial package setup and campaign handoff.',
            ],
            'power-leads' => [
                'title' => 'Growth',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/ENBVclSqwUuX7awfOEM8',
                'description' => 'Growth lead onboarding form for teams scaling lead intake and routing.',
            ],
            'prime-leads' => [
                'title' => 'Elite',
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
