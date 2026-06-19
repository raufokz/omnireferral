<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestFavouritesController extends Controller
{
    public function index(Request $request): View
    {
        $ids = json_decode($request->cookie('guest_favourites', '[]'), true) ?? [];
        $listings = collect();
        
        if (!empty($ids)) {
            $listings = Property::query()
                ->whereIn('id', $ids)
                ->where('status', 'Active')
                ->where('approval_status', 'approved')
                ->with(['realtorProfile.user', 'owner', 'listedBy'])
                ->withFavoriteSummary(null)
                ->get();
        }

        return view('pages.guest-favourites', [
            'listings' => $listings,
            'meta' => [
                'title' => 'My Favourites | OmniReferral',
                'description' => 'View your saved property listings.',
            ],
        ]);
    }
}
