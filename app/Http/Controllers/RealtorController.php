<?php

namespace App\Http\Controllers;

use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\View\View;

class RealtorController extends Controller
{
    public function index(): View
    {
        return view('pages.agents', [
            'agents' => User::query()
                ->select(['id', 'name', 'display_name', 'email', 'phone', 'role', 'status', 'avatar', 'created_at'])
                ->where('role', 'agent')
                ->where('status', 'active')
                ->whereHas('realtorProfile')
                ->with([
                    'realtorProfile' => fn ($query) => $query->select([
                        'id',
                        'user_id',
                        'slug',
                        'brokerage_name',
                        'service_city',
                        'service_state',
                        'service_zip_code',
                        'rating',
                        'specialties',
                        'bio',
                        'headshot',
                    ]),
                ])
                ->orderBy('name')
                ->paginate(9),
            'meta' => [
                'title' => 'Agent Directory | OmniReferral',
                'description' => 'Browse trusted OmniReferral partner agents by location, specialty, and performance.',
            ],
        ]);
    }

    public function show(RealtorProfile $realtor): View
    {
        $viewer = auth()->user();

        $realtor->load([
            'user',
            'properties' => fn ($query) => $query
                ->withFavoriteSummary($viewer)
                ->marketplaceVisible()
                ->latest(),
        ]);

        abort_unless(
            $realtor->user
            && $realtor->user->role === 'agent'
            && $realtor->user->status === 'active',
            404
        );

        return view('pages.agent-show', [
            'agent' => $realtor,
            'meta' => [
                'title' => $realtor->user->name . ' | OmniReferral Agent Profile',
                'description' => 'Meet ' . $realtor->user->name . ', a trusted OmniReferral real estate partner serving ' . ($realtor->service_city ?: 'your market') . ( $realtor->service_state ? ', ' . $realtor->service_state : '' ) . '.',
            ],
        ]);
    }
}
