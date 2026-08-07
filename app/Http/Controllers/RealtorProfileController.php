<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RealtorProfile;
use App\Support\AgentDirectory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RealtorProfileController extends Controller
{
    private const SLUG_SUFFIX = '-realtor';

    /**
     * Show dedicated SEO-friendly realtor profile page.
     */
    public function show(string $slug): View|RedirectResponse
    {
        if (! Str::endsWith($slug, self::SLUG_SUFFIX)) {
            // Legacy URL format (no "-realtor" suffix) — 301 redirect to the new SEO URL
            // to preserve rankings on links/bookmarks pointing at the old format.
            $legacyProfile = RealtorProfile::where('slug', $slug)->first();
            abort_unless($legacyProfile, 404);

            return redirect()->route('realtors.show', ['slug' => $legacyProfile->slug.self::SLUG_SUFFIX], 301);
        }

        $baseSlug = Str::beforeLast($slug, self::SLUG_SUFFIX);

        $profile = RealtorProfile::where('slug', $baseSlug)
            ->with([
                'user:id,name,display_name,email,phone,avatar,current_plan_id,status,city,state,zip_code,social_facebook_url,social_linkedin_url',
                'user.currentPlan:id,name,slug',
                'user.activeAgentSubscription',
            ])
            ->first();

        if (! $profile) {
            // Edge case: the profile's real slug already ends in "-realtor" (e.g. "john-realtor"),
            // so stripping the suffix over-trimmed it. Retry with the full slug as-is.
            $profile = RealtorProfile::where('slug', $slug)
                ->with([
                    'user:id,name,display_name,email,phone,avatar,current_plan_id,status,city,state,zip_code,social_facebook_url,social_linkedin_url',
                    'user.currentPlan:id,name,slug',
                    'user.activeAgentSubscription',
                ])
                ->firstOrFail();
        }

        // 1. Visibility Check: Abort 404 if profile is suspended or rejected
        if (! $profile->isPublicVisible()) {
            abort(404);
        }

        // 2. Access Control: If realtor does NOT have active subscription -> 403 Forbidden
        if (! $profile->hasActiveSubscription()) {
            abort(403, 'This profile page is reserved for realtors with an active subscription plan.');
        }

        // 3. Properties listed by this realtor (cached for performance)
        $properties = Cache::remember("realtor:properties:{$profile->id}", now()->addMinutes(15), function () use ($profile) {
            return Property::query()
                ->where(function ($query) use ($profile) {
                    $query->where('realtor_profile_id', $profile->id);
                    if ($profile->user_id) {
                        $query->orWhere('listed_by_id', $profile->user_id);
                    }
                })
                ->marketplaceVisible()
                ->latest('updated_at')
                ->get();
        });

        $card = AgentDirectory::publicCardPayload($profile);
        $agentName = $profile->user?->publicDisplayName() ?: 'Real Estate Agent';
        $location = $profile->serviceAreaLabel() ?: 'United States';
        $canonicalUrl = route('realtors.show', ['slug' => $profile->slug.self::SLUG_SUFFIX]);

        // SEO Meta Information
        $metaTitle = sprintf('%s | Realtor in %s | OmniReferral', $agentName, $location);
        $metaDescription = sprintf(
            "View %s's complete realtor profile including bio, service areas, specialties, languages, experience, recent listings, and contact information.",
            $agentName
        );

        $headshotUrl = $card['headshot_url'];

        // Structured Data: RealEstateAgent & Breadcrumbs
        $agentSchema = [
            '@context' => 'https://schema.org',
            '@type' => ['RealEstateAgent', 'LocalBusiness'],
            '@id' => $canonicalUrl . '#agent',
            'name' => $agentName,
            'url' => $canonicalUrl,
            'image' => $headshotUrl,
            'description' => Str::limit(strip_tags((string) ($profile->bio ?: $metaDescription)), 250),
            'telephone' => $profile->user?->phone,
            'email' => $profile->user?->email,
            'priceRange' => '$$$',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $profile->service_city,
                'addressRegion' => $profile->service_state,
                'postalCode' => $profile->service_zip_code,
                'addressCountry' => 'US',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) ($card['rating'] ?? '4.9'),
                'reviewCount' => (string) ($card['review_count'] ?? '12'),
                'bestRating' => '5',
            ],
            'areaServed' => collect($card['service_areas'])->map(fn ($area) => [
                '@type' => 'Place',
                'name' => $area,
            ])->values()->all(),
            'knowsAbout' => $card['specialties'],
        ];

        if (! empty($card['languages_list'])) {
            $agentSchema['knowsLanguage'] = $card['languages_list'];
        }

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Agent Directory',
                    'item' => route('agents.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $agentName,
                    'item' => $canonicalUrl,
                ],
            ],
        ];

        $stats = [
            'active_listings' => $properties->where('status', '!=', 'Sold')->count(),
            'sold_listings' => $properties->where('status', 'Sold')->count(),
            'total_leads' => (int) ($profile->leads_closed ?? 0),
            'years_experience' => max(2, (int) ($profile->years_of_experience ?? 2)),
        ];

        return view('realtors.show', [
            'profile' => $profile,
            'card' => $card,
            'user' => $profile->user,
            'properties' => $properties,
            'stats' => $stats,
            'canonicalUrl' => $canonicalUrl,
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
                'image' => $headshotUrl,
            ],
            'schemas' => [$agentSchema, $breadcrumbSchema],
        ]);
    }
}
