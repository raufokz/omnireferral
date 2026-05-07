<?php

namespace App\Http\Controllers;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RealtorController extends Controller
{
    /**
     * Public agent directory: users are the source of truth (active agents only);
     * realtor_profiles joined for slug, service area, brokerage, etc.
     */
    public function index(Request $request): View
    {
        $realtorProfileSelect = [
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
        ];

        $query = User::query()
            ->publicDirectoryAgents()
            ->with(['realtorProfile' => fn ($q) => $q->select($realtorProfileSelect)])
            ->select([
                'id',
                'name',
                'display_name',
                'email',
                'phone',
                'avatar',
                'city',
                'state',
                'zip_code',
                'role',
                'status',
                'created_at',
            ]);

        if ($search = trim((string) $request->query('q', ''))) {
            $like = '%'.$search.'%';
            $query->where(function ($w) use ($like, $search) {
                $w->where('users.name', 'like', $like)
                    ->orWhere('users.display_name', 'like', $like)
                    ->orWhere('users.email', 'like', $like)
                    ->orWhere('users.phone', 'like', $like)
                    ->orWhere('users.city', 'like', $like)
                    ->orWhere('users.state', 'like', $like)
                    ->orWhere('users.zip_code', 'like', $like);

                $w->orWhereHas('realtorProfile', function ($rp) use ($like) {
                    $rp->where('brokerage_name', 'like', $like)
                        ->orWhere('specialties', 'like', $like)
                        ->orWhere('service_city', 'like', $like)
                        ->orWhere('license_number', 'like', $like);
                });
            });
        }

        if ($city = trim((string) $request->query('city', ''))) {
            $query->whereHas('realtorProfile', fn ($rp) => $rp->whereRaw('LOWER(service_city) = ?', [mb_strtolower($city)]));
        }

        if ($specialty = trim((string) $request->query('specialty', ''))) {
            $needle = '%'.mb_strtolower($specialty).'%';
            $query->whereHas('realtorProfile', fn ($rp) => $rp->whereRaw('LOWER(specialties) LIKE ?', [$needle]));
        }

        $agents = $query
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        $filterCities = RealtorProfile::query()
            ->select('service_city')
            ->whereNotNull('approved_at')
            ->whereNotNull('service_city')
            ->whereHas('user', fn ($u) => $u->where('role', 'agent')->where('status', 'active'))
            ->distinct()
            ->orderBy('service_city')
            ->pluck('service_city')
            ->filter()
            ->values();

        $filterSpecialties = RealtorProfile::query()
            ->select('specialties')
            ->whereNotNull('approved_at')
            ->whereNotNull('specialties')
            ->whereHas('user', fn ($u) => $u->where('role', 'agent')->where('status', 'active'))
            ->distinct()
            ->orderBy('specialties')
            ->pluck('specialties')
            ->filter()
            ->values();

        return view('pages.agents', [
            'agents' => $agents,
            'filterCities' => $filterCities,
            'filterSpecialties' => $filterSpecialties,
            'meta' => [
                'title' => 'Agent Directory | OmniReferral',
                'description' => 'Browse trusted OmniReferral partner agents by location, specialty, and active listings.',
            ],
        ]);
    }

    /**
     * Public agent profile: route key is realtor_profiles.slug; resolved to User via Route::bind.
     */
    public function show(User $realtor): View
    {
        $profile = $realtor->realtorProfile;
        abort_unless($profile && $profile->approved_at, 404);

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
