<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RealtorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function show(Property $property): View
    {
        return view('pages.property-show', [
            'property' => $property->load('realtorProfile.user'),
            'relatedProperties' => Property::with('realtorProfile.user')
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
        $validated = $request->validate([
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
        ], [
            'title.required' => 'Please add a title for the property listing.',
            'image.image' => 'Please upload a valid property image file.',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('properties/listings', 'public');
            $validated['images'] = [$validated['image']];
        }

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::lower(Str::random(6));
        $validated['status'] = 'Active';
        $validated['beds'] = $validated['beds'] ?? 3;
        $validated['baths'] = $validated['baths'] ?? 2;
        $validated['sqft'] = $validated['sqft'] ?? 1500;
        $validated['description'] = $validated['description'] ?? 'Property uploaded through the OmniReferral seller or agent workspace.';
        $validated['source'] = Auth::user()?->isAgent() ? 'Agent Dashboard Upload' : 'Seller Dashboard Upload';
        $validated['published_at'] = now();
        $validated['is_featured'] = false;
        $validated['realtor_profile_id'] = Auth::user()?->realtorProfile?->id ?: RealtorProfile::query()->value('id');

        Property::create($validated);

        return redirect()->route(Auth::user()?->isAgent() ? 'dashboard.agent' : 'dashboard.seller')
            ->with('success', 'Your property listing has been uploaded and is ready to review.');
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

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:Active,Pending,Sold,Off-Market'],
            'description' => ['nullable', 'string'],
        ]);

        $property->update($validated);

        return redirect()->route(Auth::user()?->dashboardRouteName() ?? 'dashboard')
            ->with('success', 'Property listing updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        abort_unless($this->canManage($property), 403, 'Unauthorized action.');

        $property->delete();

        return back()->with('success', 'Property listing removed.');
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

        if ($user->role === 'agent' && $user->realtorProfile) {
            return $property->realtor_profile_id === $user->realtorProfile->id;
        }

        return false;
    }
}
