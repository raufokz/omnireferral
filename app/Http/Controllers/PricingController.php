<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\StripeCheckoutService;
use App\Support\AgentDirectory;
use App\Support\PricingContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(): View
    {
        $pricingPlans = PricingContent::plans();
        $featuredAgents = AgentDirectory::publicQuery()
            ->with(['user:id,name,display_name,avatar,status,current_plan_id', 'user.currentPlan:id,name,slug'])
            ->select([
                'realtor_profiles.id', 'realtor_profiles.user_id', 'realtor_profiles.slug',
                'realtor_profiles.brokerage_name', 'realtor_profiles.service_city',
                'realtor_profiles.service_state', 'realtor_profiles.service_zip_code',
                'realtor_profiles.rating', 'realtor_profiles.review_count',
                'realtor_profiles.leads_closed', 'realtor_profiles.specialties',
                'realtor_profiles.bio', 'realtor_profiles.headshot',
                'realtor_profiles.profile_status', 'realtor_profiles.years_of_experience',
                'realtor_profiles.languages', 'realtor_profiles.market_areas',
                'realtor_profiles.social_links', 'realtor_profiles.created_at',
            ])
            ->orderedForDirectory()
            ->take(3)
            ->get();

        return view('pages.pricing', [
            'pricingPlans' => $pricingPlans,
            'leadPlans' => array_values($pricingPlans['real_estate'] ?? []),
            'vaPlans' => array_values($pricingPlans['virtual_assistance'] ?? []),
            'featuredAgents' => $featuredAgents,
            'meta' => [
                'title' => 'Pricing | OmniReferral Lead Packages',
                'description' => 'Compare OmniReferral Starter Lead, Growth Lead, and Elite Lead packages for real estate teams that want qualified referrals and operational follow-up.',
            ],
        ]);
    }

    public function checkout(string $packageSlug): View
    {
        $package = $this->checkoutPackage($packageSlug);
        $pricingPlan = PricingContent::planBySlug($package->slug);
        $displaySlug = $pricingPlan['slug'] ?? $this->normalizePackageSlug($package->slug);
        $packageEmbed = $this->packageEmbeds()[$displaySlug] ?? [];
        $postPurchaseAction = $this->postPurchaseAction();
        $packageDisplay = [
            'slug' => $pricingPlan['slug'] ?? $package->slug,
            'name' => $pricingPlan['name'] ?? $package->name,
            'tier' => $pricingPlan['tier'] ?? ($package->category === 'lead' ? 'Lead Package' : 'Virtual Assistance'),
            'summary' => $pricingPlan['summary'] ?? $package->description,
            'card_description' => $pricingPlan['card_description'] ?? null,
            'price' => $pricingPlan['price'] ?? ($package->preferredCheckoutAmount() ?? 0),
            'price_note' => $pricingPlan['price_note'] ?? ($package->one_time_price ? '/ One-Time' : ($package->hourly_price ? '/ Hour' : '/ Monthly')),
            'billing_label' => $pricingPlan['billing_label'] ?? $this->billingLabel($pricingPlan['price_note'] ?? null, $package),
            'badge' => $pricingPlan['badge'] ?? $pricingPlan['card_tag'] ?? $pricingPlan['tier'] ?? null,
            'ribbon_label' => $pricingPlan['ribbon_label'] ?? null,
            'savings_label' => $pricingPlan['savings_label'] ?? null,
            'guarantee_label' => $pricingPlan['guarantee_label'] ?? null,
            'features' => $pricingPlan['features'] ?? ($package->features ?? []),
            'value_price' => $pricingPlan['value_price'] ?? null,
            'cta_label' => $pricingPlan['cta_label'] ?? $package->cta_label,
            'cta_url' => $pricingPlan['cta_url'] ?? null,
            'is_featured' => $pricingPlan['is_featured'] ?? $package->is_featured,
            'highlights' => $pricingPlan['highlights'] ?? [],
            'best_for' => $pricingPlan['best_for'] ?? null,
            'what_you_get' => $pricingPlan['what_you_get'] ?? null,
            'package_benefits' => $pricingPlan['package_benefits'] ?? [],
            'after_submission' => $pricingPlan['after_submission'] ?? [],
            'support_details' => $pricingPlan['support_details'] ?? null,
            'trust_indicators' => $pricingPlan['trust_indicators'] ?? [],
            'feature_groups' => $pricingPlan['feature_groups'] ?? [],
            'trust_note' => $pricingPlan['trust_note'] ?? null,
        ];
        $packageEmbed = [
            'title' => $packageEmbed['title'] ?? $packageDisplay['name'],
            'src' => $packageEmbed['src'] ?? $package->ghl_form_url,
            'description' => $packageEmbed['description']
                ?? ($packageDisplay['summary'] ?: 'Complete the secure setup form so OmniReferral can provision your package correctly.'),
        ];

        return view('pages.package-checkout', [
            'package' => $package,
            'packageDisplay' => $packageDisplay,
            'packageEmbed' => $packageEmbed,
            'postPurchaseActionUrl' => $postPurchaseAction['url'],
            'postPurchaseActionLabel' => $postPurchaseAction['label'],
            'meta' => [
                'title' => $packageDisplay['name'] . ' Checkout | OmniReferral',
                'description' => 'Review the ' . $packageDisplay['name'] . ' package and complete the secure GoHighLevel form.',
            ],
        ]);
    }

    public function stripeCheckout(string $packageSlug): RedirectResponse
    {
        $package = $this->checkoutPackage($packageSlug);
        $user = Auth::user();
        $checkout = app(StripeCheckoutService::class);

        if (! $checkout->configured()) {
            return redirect()
                ->route('packages.checkout', $packageSlug)
                ->with('error', 'Payment processing is currently unavailable. Please contact support.');
        }

        $successUrl = route('client.form.submission').'?session_id={CHECKOUT_SESSION_ID}&package='.$packageSlug;
        $cancelUrl = route('packages.checkout', $packageSlug);

        $session = $checkout->createPackageCheckout($package, $user, [
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'billing' => $package->billing_type,
            'customer_email' => $user?->email,
        ]);

        if (! $session) {
            return redirect()
                ->route('packages.checkout', $packageSlug)
                ->with('error', 'Unable to create payment session. Please try again or contact support.');
        }

        return redirect()->away($session->url);
    }

    public function success(string $packageSlug): RedirectResponse
    {
        $sessionId = request()->query('session_id');
        $params = array_filter([
            'session_id' => $sessionId,
            'package'    => $packageSlug,
        ]);

        return redirect()->route('client.form.submission', $params);
    }

    private function packageEmbeds(): array
    {
        return [
            'starter-leads' => [
                'title' => 'Starter Lead',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/q61dioT6A8taz0yLfK93',
                'description' => 'Starter Lead onboarding form for verified referral growth and market launch setup.',
            ],
            'growth-leads' => [
                'title' => 'Growth Lead',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/ENBVclSqwUuX7awfOEM8',
                'description' => 'Growth Lead onboarding form for balanced growth, visibility, and scaling team support.',
            ],
            'elite-leads' => [
                'title' => 'Elite Lead',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/z2wUhJG00x4n3MxY616R',
                'description' => 'Elite Lead onboarding form for premium referral exposure and priority support workflows.',
            ],
            'va-starter' => [
                'title' => 'ISA Support',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/DAYWVBJkNiVLEfoW740d',
                'description' => 'Cold Calling / ISA onboarding form for dedicated prospecting, follow-up, and pipeline growth setup.',
            ],
            'va-growth' => [
                'title' => 'Full Social Media Package',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/NiEcLMPWI084aKiAaNsi',
                'description' => 'Social Media Management onboarding form for content, audience growth, and brand visibility.',
            ],
            'individual-va' => [
                'title' => 'Individual VA',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/DAnafQ8CfUsIMsj8Zq4D',
                'description' => 'Individual VA onboarding form for flexible hourly virtual assistant support and task setup.',
            ],
            'va-calling' => [
                'title' => 'Cold Calling / ISA',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/DAYWVBJkNiVLEfoW740d',
                'description' => 'Cold Calling / ISA onboarding form for dedicated prospecting, follow-up, and pipeline growth setup.',
            ],
            'va-social' => [
                'title' => 'Social Media Mgmt',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/NiEcLMPWI084aKiAaNsi',
                'description' => 'Social Media Management onboarding form for content, audience growth, and brand visibility.',
            ],
            'va-individual' => [
                'title' => 'Individual VA',
                'src' => 'https://api.leadconnectorhq.com/widget/survey/DAnafQ8CfUsIMsj8Zq4D',
                'description' => 'Individual VA onboarding form for flexible hourly virtual assistant support and task setup.',
            ],
        ];
    }

    private function checkoutPackage(string $packageSlug, bool $persist = false): Package
    {
        $slug = Str::slug($packageSlug);
        $package = Package::query()->where('slug', $slug)->first();

        if ($package) {
            return $package;
        }

        $legacySlug = $this->legacyPackageSlug($slug);
        if ($legacySlug !== null) {
            $legacyPackage = Package::query()->where('slug', $legacySlug)->first();
            if ($legacyPackage) {
                return $legacyPackage;
            }
        }

        $pricingPlan = PricingContent::planBySlug($slug);
        abort_unless($pricingPlan, 404);

        $attributes = $this->packageAttributesFromPricingPlan($pricingPlan);

        if ($persist) {
            return Package::query()->create($attributes);
        }

        return new Package($attributes);
    }

    private function packageAttributesFromPricingPlan(array $pricingPlan): array
    {
        $slug = (string) ($pricingPlan['slug'] ?? '');
        $price = max(0, (int) ($pricingPlan['price'] ?? 0));
        $priceNote = strtolower((string) ($pricingPlan['price_note'] ?? ''));
        $isMonthly = str_contains($priceNote, 'month');
        $isHourly = str_contains($priceNote, 'hour');
        $embed = $this->packageEmbeds()[$slug] ?? [];

        return [
            'name' => (string) ($pricingPlan['name'] ?? Str::headline($slug)),
            'slug' => $slug,
            'description' => (string) ($pricingPlan['summary'] ?? ''),
            'category' => ($pricingPlan['category'] ?? null) === 'virtual_assistance' ? 'virtual_assistant' : 'lead',
            'billing_type' => $isHourly ? 'hourly' : ($isMonthly ? 'monthly' : 'one_time'),
            'is_featured' => (bool) ($pricingPlan['is_featured'] ?? false),
            'is_active' => true,
            'one_time_price' => $isMonthly || $isHourly ? null : $price,
            'monthly_price' => $isMonthly ? $price : null,
            'hourly_price' => $isHourly ? $price : null,
            'ghl_form_url' => $embed['src'] ?? null,
            'ghl_pipeline_stage' => $slug,
            'features' => array_values((array) ($pricingPlan['features'] ?? [])),
            'cta_label' => (string) ($pricingPlan['cta_label'] ?? 'Get Started'),
            'duration_days' => 30,
            'sort_order' => (int) ($pricingPlan['sort_order'] ?? 100),
        ];
    }

    private function legacyPackageSlug(string $slug): ?string
    {
        return match ($slug) {
            'starter-leads' => 'quick-leads',
            'growth-leads' => 'power-leads',
            'elite-leads' => 'prime-leads',
            default => null,
        };
    }

    private function normalizePackageSlug(string $slug): string
    {
        return match ($slug) {
            'quick-leads' => 'starter-leads',
            'power-leads' => 'growth-leads',
            'prime-leads' => 'elite-leads',
            default => $slug,
        };
    }

    private function billingLabel(?string $priceNote, Package $package): string
    {
        $note = trim(str_replace('/', '', (string) $priceNote));

        if ($note !== '') {
            return ucfirst($note);
        }

        return match ($package->billing_type) {
            'monthly' => 'Monthly package',
            'hourly' => 'Hourly support',
            default => 'One-time package',
        };
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
