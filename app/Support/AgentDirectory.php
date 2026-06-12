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
            ->publicVisible();
    }


    public static function applyFeaturedSort(Builder $query): Builder
    {
        return $query
            ->orderByDesc('rating')
            ->orderByDesc('created_at');
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
                ->where('brokerage_name', 'like', $like)
                ->orWhere('specialties', 'like', $like)
                ->orWhere('service_city', 'like', $like)
                ->orWhere('bio', 'like', $like)
                ->orWhereHas('user', function (Builder $userQuery) use ($like) {
                    $userQuery
                        ->where('name', 'like', $like)
                        ->orWhere('display_name', 'like', $like);
                });
        });
    }

    public static function applyLocationFilter(Builder $query, ?string $state, ?string $city): Builder
    {
        if ($state) {
            $query->whereRaw('UPPER(service_state) = ?', [strtoupper($state)]);
        }

        if ($city) {
            $query->whereRaw('LOWER(service_city) = ?', [mb_strtolower($city)]);
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

        return [
            'id' => $profile->id,
            'slug' => $profile->slug,
            'name' => $user?->publicDisplayName() ?: 'Real Estate Agent',
            'brokerage' => $profile->brokerage_name ?: 'Independent Brokerage',
            'city' => $profile->service_city,
            'state' => $profile->service_state,
            'service_area' => $profile->serviceAreaLabel(),

            'rating' => number_format((float) ($profile->rating ?? 0), 1),
            'review_count' => (int) ($profile->review_count ?? 0),
            'specialties' => $profile->specialtiesList(),
            'specialties_text' => $profile->specialties,
            'bio' => $profile->bio,
            'languages' => $profile->languages,
            'market_areas' => $profile->market_areas,
            'is_featured' => false,

            'headshot_url' => $profile->headshotPublicUrl($user),
            'profile_url' => route('agents.profile', $profile),
        ];
    }
}
