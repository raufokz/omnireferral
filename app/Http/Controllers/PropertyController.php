<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RealtorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function show(Property $property): View
    {
        $viewer = Auth::user();

        abort_unless(
            ($property->isApproved() && $property->status === 'Active') || $this->canManage($property),
            404
        );

        $property->load('realtorProfile.user')
            ->loadCount(['favorites as favorites_count']);

        $property->setAttribute(
            'is_favorited',
            $viewer ? $property->favorites()->where('user_id', $viewer->id)->exists() : false
        );

        return view('pages.property-details', [
            'property' => $property,
            'relatedProperties' => Property::query()
                ->with('realtorProfile.user')
                ->withFavoriteSummary($viewer)
                ->marketplaceVisible()
                ->whereKeyNot($property->id)
                ->where('zip_code', $property->zip_code)
                ->latest()
                ->take(3)
                ->get(),
            'meta' => [
                'title' => $property->title . ' | OmniReferral Listing',
                'description' => 'View property details, pricing, location, and connected agent information for ' . $property->title . '.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'zip_code' => ['required', 'string', 'max:10'],
            'property_type' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer'],
            'beds' => ['nullable', 'integer', 'min:0'],
            'baths' => ['nullable', 'numeric', 'min:0'],
            'sqft' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];

        if ($user?->isSeller()) {
            $rules['listing_realtor_profile_id'] = ['required', 'integer', 'exists:realtor_profiles,id'];
        }

        $validated = $request->validate($rules, [
            'title.required' => 'Please add a title for the property listing.',
            'image.image' => 'Please upload a valid property image file.',
            'listing_realtor_profile_id.required' => 'Choose the OmniReferral agent who will represent this listing.',
        ]);

        if ($user?->isAgent()) {
            $profile = $this->ensureAgentProfile($user);
            $activePlan = $user->currentPlan && $user->currentPlan->category === 'lead'
                ? $user->currentPlan
                : null;
            $listingLimit = $activePlan?->listingLimit() ?? 0;

            if ($listingLimit < 1) {
                return redirect()
                    ->route('agent.listings.index')
                    ->with('error', 'Your current package does not include listing access yet. Upgrade your plan to publish listings.');
            }

            $activeListingCount = Property::query()
                ->where('realtor_profile_id', $profile->id)
                ->where('approval_status', '!=', Property::APPROVAL_REJECTED)
                ->whereNotIn('status', ['Sold', 'Off-Market'])
                ->count();

            if ($activeListingCount >= $listingLimit) {
                return redirect()
                    ->route('agent.listings.index')
                    ->with('error', 'You have reached the active listing limit for your ' . $activePlan->name . ' package.');
            }

            $validated['source'] = 'Agent Dashboard Upload';
            $validated['realtor_profile_id'] = $profile->id;
            $validated['owner_user_id'] = $user->id;
        }

        if ($user?->isSeller()) {
            $validated['realtor_profile_id'] = (int) $validated['listing_realtor_profile_id'];
            unset($validated['listing_realtor_profile_id']);
            $validated['owner_user_id'] = $user->id;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('properties/listings', 'public');
            $validated['images'] = [$validated['image']];
        }

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::lower(Str::random(6));
        $requiresApproval = $user?->isAgent() || $user?->isSeller();
        $validated['status'] = $requiresApproval ? 'Pending' : 'Active';
        $validated['approval_status'] = $requiresApproval ? Property::APPROVAL_PENDING : Property::APPROVAL_APPROVED;
        $validated['approval_notes'] = null;
        $validated['beds'] = $validated['beds'] ?? 3;
        $validated['baths'] = $validated['baths'] ?? 2;
        $validated['sqft'] = $validated['sqft'] ?? 1500;
        $validated['description'] = $validated['description'] ?? 'Property uploaded through the OmniReferral seller or agent workspace.';
        $validated['source'] = $validated['source'] ?? ($user?->isAgent() ? 'Agent Dashboard Upload' : 'Seller Dashboard Upload');
        $validated['published_at'] = $requiresApproval ? null : now();
        $validated['is_featured'] = false;
        $validated['reviewed_by_user_id'] = $requiresApproval ? null : $user?->id;
        $validated['reviewed_at'] = $requiresApproval ? null : now();

        if (! isset($validated['realtor_profile_id'])) {
            return back()
                ->withInput()
                ->with('error', 'A listing agent profile is required before this property can be saved.');
        }

        Property::create($validated);

        return redirect()->route($user?->isAgent() ? 'agent.listings.index' : 'dashboard.seller.listings')
            ->with('success', $requiresApproval
                ? 'Your property listing has been submitted for admin review.'
                : 'Your property listing is live.');
    }

    public function edit(Property $property): View
    {
        abort_unless($this->canManage($property), 403, 'Unauthorized action.');

        return view('pages.dashboards.property-edit', [
            'property' => $property,
            'meta' => [
                'title' => 'Edit Property | OmniReferral',
            ],
        ]);
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        abort_unless($this->canManage($property), 403, 'Unauthorized action.');

        $user = $request->user();
        $statusOptions = $user?->isStaff()
            ? ['Active', 'Pending', 'Sold', 'Off-Market']
            : ['Active', 'Sold', 'Off-Market'];

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer'],
            'status' => ['required', 'string', Rule::in($statusOptions)],
            'description' => ['nullable', 'string'],
        ]);

        $shouldResubmit = ! $user?->isStaff() && $property->approval_status !== Property::APPROVAL_APPROVED;

        if ($shouldResubmit) {
            $validated['status'] = 'Pending';
            $validated['approval_status'] = Property::APPROVAL_PENDING;
            $validated['approval_notes'] = null;
            $validated['reviewed_by_user_id'] = null;
            $validated['reviewed_at'] = null;
            $validated['published_at'] = null;
        }

        $property->update($validated);

        return redirect()->route(match (true) {
            $user?->isAgent() => 'agent.listings.index',
            $user?->isSeller() => 'dashboard.seller.listings',
            $user?->isStaff() => 'admin.dashboard',
            default => 'dashboard',
        })
            ->with('success', $shouldResubmit
                ? 'Property listing updated and resubmitted for admin review.'
                : 'Property listing updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        abort_unless($this->canManage($property), 403, 'Unauthorized action.');

        $property->delete();

        return back()->with('success', 'Property listing removed.');
    }

    public function review(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->isStaff(), 403, 'Unauthorized action.');

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $isApproval = $validated['decision'] === 'approve';

        $property->update([
            'approval_status' => $isApproval ? Property::APPROVAL_APPROVED : Property::APPROVAL_REJECTED,
            'approval_notes' => $validated['approval_notes'] ?? null,
            'reviewed_by_user_id' => $user->id,
            'reviewed_at' => now(),
            'published_at' => $isApproval ? ($property->published_at ?? now()) : null,
            'status' => $isApproval
                ? (in_array($property->status, ['Sold', 'Off-Market'], true) ? $property->status : 'Active')
                : 'Pending',
        ]);

        return back()->with(
            'success',
            $isApproval
                ? 'Listing approved and moved into the public marketplace.'
                : 'Listing rejected. The agent can update it and resubmit for review.'
        );
    }

    public function toggleFavorite(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        abort_unless($property->isApproved() && $property->status === 'Active', 404);

        $favorite = $property->favorites()->where('user_id', $user->id)->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', 'Property removed from favorites.');
        }

        $property->favorites()->create([
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'Property added to favorites.');
    }

    protected function canManage(Property $property): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'staff'])) {
            return true;
        }

        if ($user->isSeller() && (int) $property->owner_user_id === (int) $user->id) {
            return true;
        }

        if ($user->role === 'agent' && $user->realtorProfile) {
            return $property->realtor_profile_id === $user->realtorProfile->id;
        }

        return false;
    }

    protected function ensureAgentProfile($user): RealtorProfile
    {
        return $user->realtorProfile ?: RealtorProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => Str::slug($user->name . '-' . Str::lower(Str::random(6))),
                'brokerage_name' => 'OmniReferral Partner',
                'license_number' => 'Pending',
                'address_line_1' => $user->address_line_1,
                'address_line_2' => $user->address_line_2,
                'city' => $user->city ?: 'Dallas',
                'state' => $user->state ?: 'TX',
                'zip_code' => $user->zip_code ?: '75201',
                'specialties' => 'Buyer Representation, Seller Strategy, Lead Conversion',
                'bio' => 'Agent profile created in the OmniReferral workspace.',
                'headshot' => $user->avatar ? 'storage/' . ltrim($user->avatar, '/') : 'images/realtors/3.png',
            ]
        );
    }
}
