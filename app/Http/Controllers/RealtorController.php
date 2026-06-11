<?php

namespace App\Http\Controllers;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RealtorController extends Controller
{
    /**
     * Public agent directory: only approved, complete, active agent profiles.
     */
    public function index(Request $request): View
    {
        $query = RealtorProfile::query()
            ->publicEligible()
            ->with(['user' => fn ($userQuery) => $userQuery->select([
                'id',
                'name',
                'display_name',
                'phone',
                'avatar',
                'city',
                'state',
                'zip_code',
                'role',
                'status',
            ])])
            ->select([
                'id',
                'user_id',
                'slug',
                'brokerage_name',
                'license_number',
                'service_city',
                'service_state',
                'service_zip_code',
                'rating',
                'review_count',
                'leads_closed',
                'specialties',
                'bio',
                'headshot',
                'approved_at',
            ]);

        if ($search = trim((string) $request->query('q', ''))) {
            $like = '%'.$search.'%';
            $query->where(function ($profileQuery) use ($like) {
                $profileQuery->where('brokerage_name', 'like', $like)
                    ->orWhere('specialties', 'like', $like)
                    ->orWhere('service_city', 'like', $like)
                    ->orWhere('license_number', 'like', $like)
                    ->orWhere('bio', 'like', $like)
                    ->orWhereHas('user', function ($userQuery) use ($like) {
                        $userQuery->where('name', 'like', $like)
                            ->orWhere('display_name', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });
            });
        }

        if ($city = trim((string) $request->query('city', ''))) {
            $query->whereRaw('LOWER(service_city) = ?', [mb_strtolower($city)]);
        }

        if ($specialty = trim((string) $request->query('specialty', ''))) {
            $needle = '%'.mb_strtolower($specialty).'%';
            $query->whereRaw('LOWER(specialties) LIKE ?', [$needle]);
        }

        $profiles = $query
            ->orderByDesc('rating')
            ->orderBy('service_city')
            ->paginate(9)
            ->withQueryString();

        $filterCities = RealtorProfile::query()
            ->publicEligible()
            ->select('service_city')
            ->distinct()
            ->orderBy('service_city')
            ->pluck('service_city')
            ->filter()
            ->values();

        $filterSpecialties = RealtorProfile::query()
            ->publicEligible()
            ->select('specialties')
            ->distinct()
            ->orderBy('specialties')
            ->pluck('specialties')
            ->filter()
            ->values();

        return view('pages.agents', [
            'profiles' => $profiles,
            'filterCities' => $filterCities,
            'filterSpecialties' => $filterSpecialties,
            'meta' => [
                'title' => 'Agent Directory | OmniReferral',
                'description' => 'Browse trusted OmniReferral partner agents by location, specialty, and active listings.',
            ],
        ]);
    }

    /**
     * Public agent profile resolved via Route::bind('realtor').
     */
    public function show(User $realtor): View
    {
        $profile = $realtor->realtorProfile;
        abort_unless($profile && $profile->isApprovedForPublicShow(), 404);

        $viewer = auth()->user();

        $profile->load([
            'properties' => fn ($q) => $q
                ->withFavoriteSummary($viewer)
                ->marketplaceVisible()
                ->latest(),
        ]);

        return view('pages.agent-show', [
            'user' => $realtor,
            'profile' => $profile,
            'meta' => [
                'title' => $realtor->publicDisplayName().' | OmniReferral Agent Profile',
                'description' => 'Meet '.$realtor->publicDisplayName().', a trusted OmniReferral real estate partner serving '
                    .($profile->service_city ?: 'your market')
                    .($profile->service_state ? ', '.$profile->service_state : '').'.',
            ],
        ]);
    }
}
