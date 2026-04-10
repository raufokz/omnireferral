<?php

namespace App\Http\Controllers;

use App\Models\RealtorProfile;
use Illuminate\View\View;

class RealtorController extends Controller
{
    public function index(): View
    {
        return view('pages.agents', [
            'agents' => RealtorProfile::with('user')->latest()->paginate(9),
            'meta' => [
                'title' => 'Agent Directory | OmniReferral',
                'description' => 'Browse trusted OmniReferral partner agents by location, specialty, and performance.',
            ],
        ]);
    }

    public function show(RealtorProfile $realtor): View
    {
        $realtor->load([
            'user',
            'properties' => fn ($query) => $query->marketplaceVisible()->latest(),
        ]);

        return view('pages.agent-show', [
            'agent' => $realtor,
            'meta' => [
                'title' => $realtor->user->name . ' | OmniReferral Agent Profile',
                'description' => 'Meet ' . $realtor->user->name . ', a trusted OmniReferral real estate partner serving ' . $realtor->city . ', ' . $realtor->state . '.',
            ],
        ]);
    }
}
