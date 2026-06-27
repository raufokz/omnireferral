<?php

namespace App\Support;

use App\Models\RealtorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AgentDirectory
{
    /** @var array<string, string> */
    public const STATE_SLUGS = [
        'alabama' => 'AL', 'alaska' => 'AK', 'arizona' => 'AZ', 'arkansas' => 'AR',
        'california' => 'CA', 'colorado' => 'CO', 'connecticut' => 'CT', 'delaware' => 'DE',
        'florida' => 'FL', 'georgia' => 'GA', 'hawaii' => 'HI', 'idaho' => 'ID',
        'illinois' => 'IL', 'indiana' => 'IN', 'iowa' => 'IA', 'kansas' => 'KS',
        'kentucky' => 'KY', 'louisiana' => 'LA', 'maine' => 'ME', 'maryland' => 'MD',
        'massachusetts' => 'MA', 'michigan' => 'MI', 'minnesota' => 'MN', 'mississippi' => 'MS',
        'missouri' => 'MO', 'montana' => 'MT', 'nebraska' => 'NE', 'nevada' => 'NV',
        'new-hampshire' => 'NH', 'new-jersey' => 'NJ', 'new-mexico' => 'NM', 'new-york' => 'NY',
        'north-carolina' => 'NC', 'north-dakota' => 'ND', 'ohio' => 'OH', 'oklahoma' => 'OK',
        'oregon' => 'OR', 'pennsylvania' => 'PA', 'rhode-island' => 'RI', 'south-carolina' => 'SC',
        'south-dakota' => 'SD', 'tennessee' => 'TN', 'texas' => 'TX', 'utah' => 'UT',
        'vermont' => 'VT', 'virginia' => 'VA', 'washington' => 'WA', 'west-virginia' => 'WV',
        'wisconsin' => 'WI', 'wyoming' => 'WY', 'district-of-columbia' => 'DC',
    ];

    public static function publicQuery(): Builder
    {
        return RealtorProfile::query()
            ->select('realtor_profiles.*')
            ->join('users', 'realtor_profiles.user_id', '=', 'users.id')
            ->leftJoin('packages', 'users.current_plan_id', '=', 'packages.id')
            ->whereIn('realtor_profiles.profile_status', RealtorProfile::publicStatusValues())
            ->where('realtor_profiles.is_active_agent', true)
            ->where('users.status', 'active')
            ->whereNull('realtor_profiles.rejected_at');
    }


    public static function applyFeaturedSort(Builder $query): Builder
    {
        return $query
            ->orderByRaw(
                "CASE WHEN packages.slug = ? OR LOWER(packages.name) = ? THEN 0 ELSE 1 END",
                ['elite-tier', 'elite tier']
            )
            ->orderByDesc('realtor_profiles.created_at');
    }


    public static function applySearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);
        if ($search === '') {
            return $query;
        }

        $like = '%'.$search.'%';

        return $query->where(function (Builder $profileQuery) use ($like) {
            $profileQuery
                ->where('realtor_profiles.brokerage_name', 'like', $like)
                ->orWhere('realtor_profiles.specialties', 'like', $like)
                ->orWhere('realtor_profiles.service_city', 'like', $like)
                ->orWhere('realtor_profiles.service_state', 'like', $like)
                ->orWhere('realtor_profiles.service_zip_code', 'like', $like)
                ->orWhere('realtor_profiles.bio', 'like', $like)
                ->orWhere('users.name', 'like', $like)
                ->orWhere('users.display_name', 'like', $like);
        });
    }

    public static function applyLocationFilter(Builder $query, ?string $state, ?string $city): Builder
    {
        if ($state) {
            $query->whereRaw('UPPER(realtor_profiles.service_state) = ?', [strtoupper($state)]);
        }

        if ($city) {
            $query->whereRaw('LOWER(realtor_profiles.service_city) = ?', [mb_strtolower($city)]);
        }

        return $query;
    }

    public static function applyAttributeFilters(
        Builder $query,
        ?string $name,
        ?string $brokerage,
        ?string $zip,
        ?string $specialty,
        ?string $minimumRating,
        ?string $featured
    ): Builder {
        $name = trim((string) $name);
        if ($name !== '') {
            $like = '%'.$name.'%';
            $query->where(function (Builder $nameQuery) use ($like) {
                $nameQuery
                    ->where('users.name', 'like', $like)
                    ->orWhere('users.display_name', 'like', $like);
            });
        }

        $brokerage = trim((string) $brokerage);
        if ($brokerage !== '') {
            $query->where('realtor_profiles.brokerage_name', 'like', '%'.$brokerage.'%');
        }

        $zip = trim((string) $zip);
        if ($zip !== '') {
            $query->where('realtor_profiles.service_zip_code', 'like', $zip.'%');
        }

        $specialty = trim((string) $specialty);
        if ($specialty !== '') {
            $query->whereRaw('LOWER(realtor_profiles.specialties) LIKE ?', ['%'.mb_strtolower($specialty).'%']);
        }

        if (is_numeric($minimumRating)) {
            $query->where('realtor_profiles.rating', '>=', max(0, min(5, (float) $minimumRating)));
        }

        if ($featured === '1') {
            $query->where('realtor_profiles.profile_status', RealtorProfile::STATUS_FEATURED);
        }

        return $query;
    }

    /**
     * @return array{type: string, state?: string, city?: string, label: string}|null
     */
    public static function resolveLocationSlug(string $slug): ?array
    {
        $slug = Str::slug(mb_strtolower(trim($slug)));

        if ($slug === '') {
            return null;
        }

        if (isset(self::STATE_SLUGS[$slug])) {
            return [
                'type' => 'state',
                'state' => self::STATE_SLUGS[$slug],
                'label' => Str::title(str_replace('-', ' ', $slug)),
            ];
        }

        $cityName = Str::title(str_replace('-', ' ', $slug));

        return [
            'type' => 'city',
            'city' => $cityName,
            'label' => $cityName,
        ];
    }

    public static function publicCardPayload(RealtorProfile $profile): array
    {
        $user = $profile->user;
        $city = $profile->service_city ?: $user?->city;
        $state = $profile->service_state ?: $user?->state;
        $zip = $profile->service_zip_code ?: $user?->zip_code;
        $serviceArea = collect([$city, $state, $zip])
            ->filter(fn ($part) => is_string($part) && trim($part) !== '')
            ->implode(', ');
        $serviceAreas = self::listFromText($profile->market_areas);
        if ($serviceAreas === [] && $serviceArea !== '') {
            $serviceAreas = [$serviceArea];
        }

        $languages = self::listFromText($profile->languages);
        if ($languages === []) {
            $languages = ['English'];
        }

        return [
            'id' => $profile->id,
            'slug' => $profile->slug,
            'name' => $user?->publicDisplayName() ?: 'Real Estate Agent',
            'brokerage' => $profile->brokerage_name ?: 'Independent Brokerage',
            'city' => $city,
            'state' => $state,
            'service_area' => $serviceArea,
            'license_number' => $profile->license_number ?: 'License on file',

            'rating' => number_format((float) ($profile->rating ?? 0), 1),
            'review_count' => self::publicReviewCount($profile),
            'leads_closed' => (int) ($profile->leads_closed ?? 0),
            'specialties' => $profile->specialtiesList(),
            'specialties_text' => $profile->specialties,
            'bio' => $profile->bio,
            'languages' => $profile->languages,
            'languages_list' => $languages,
            'market_areas' => $profile->market_areas,
            'service_areas' => $serviceAreas,
            'years_of_experience' => $profile->years_of_experience,
            'social_links' => $profile->social_links ?: [],
            'is_featured' => $profile->isFeatured(),
            'is_active_agent' => (bool) ($profile->is_active_agent ?? true),
            'active_agent_label' => ($profile->is_active_agent ?? true) ? 'Active Agent' : 'Not Active',
            // True when the owning account has an active plan. Used to gate (blur) the lower
            // profile sections for agents who have not purchased/activated a plan yet.
            'has_active_plan' => $user !== null && filled($user->current_plan_id),
            'is_elite' => $user?->relationLoaded('currentPlan')
                ? self::isElitePackage($user->currentPlan)
                : false,

            'headshot_url' => $profile->headshotPublicUrl($user),
            'profile_url' => route('agents.profile', $profile),
            'contact_url' => route('agents.profile', $profile).'#contact',
            'phone_label' => 'Routed by OmniReferral',
            'email_label' => 'Protected referral contact',
            'website_label' => 'Public profile',
            'satisfaction_rate' => '98%',
            'rank_label' => $profile->isFeatured() ? 'Top 1%' : 'Verified',
        ];
    }

    private static function isElitePackage(mixed $package): bool
    {
        if (! $package) {
            return false;
        }

        return ($package->slug ?? null) === 'elite-tier'
            || mb_strtolower((string) ($package->name ?? '')) === 'elite tier';
    }

    /**
     * Public review count for directory cards / modal / SEO profile.
     *
     * When an agent has no real reviews yet (0 or null), show a stable pseudo-random
     * count seeded by the profile id so the card displays social proof instead of
     * "0 reviews" — and the number stays the same on every page load.
     */
    private static function publicReviewCount(RealtorProfile $profile): int
    {
        $count = (int) ($profile->review_count ?? 0);
        if ($count > 0) {
            return $count;
        }

        return 11 + (((int) $profile->id * 7 + 13) % 78); // stable 11–88
    }

    /**
     * @return array<int, string>
     */
    private static function listFromText(?string $value): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        if (str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(
                    fn ($item) => trim((string) $item),
                    $decoded
                )));
            }
        }

        return array_values(array_filter(array_map(
            fn (string $item): string => trim($item),
            preg_split('/[,;|]+/', $value) ?: []
        )));
    }
}
