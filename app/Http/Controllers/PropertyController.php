<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\RealtorProfile;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function show(Request $request, Property $property): View
    {
        $viewer = Auth::user();

        if (! $property->isApproved() || $property->status !== 'Active') {
            $this->authorize('view', $property);
        }

        $property->load(['realtorProfile.user', 'owner', 'listedBy'])
            ->load(['listingComments' => fn ($q) => $q->with('user')->latest()])
            ->loadCount(['favorites as favorites_count']);

        $deviceId = $request->attributes->get('listing_device_id');
        if ($deviceId && $viewer) {
            $isFavorited = $property->favorites()
                ->where(function ($q) use ($deviceId, $viewer) {
                    $q->where('device_fingerprint', $deviceId)
                        ->orWhere('user_id', $viewer->id);
                })
                ->exists();
        } elseif ($deviceId) {
            $isFavorited = $property->favorites()->where('device_fingerprint', $deviceId)->exists();
        } elseif ($viewer) {
            $isFavorited = $property->favorites()->where('user_id', $viewer->id)->exists();
        } else {
            $isFavorited = false;
        }
        $property->setAttribute('is_favorited', $isFavorited);

        return view('pages.property-details', [
            'property' => $property,
            'relatedProperties' => Property::query()
                ->with(['realtorProfile.user', 'owner', 'listedBy'])
                ->withFavoriteSummary($viewer)
                ->marketplaceVisible()
                ->whereKeyNot($property->id)
                ->where('zip_code', $property->zip_code)
                ->latest()
                ->take(3)
                ->get(),
            'meta' => [
                'title' => $property->title.' | OmniReferral Listing',
                'description' => 'View property details, pricing, location, and connected agent information for '.$property->title.'.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 403);
        $this->authorize('create', Property::class);

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
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:6144'],
            'image' => ['nullable', 'image', 'max:6144'],
            'featured_image' => ['nullable', 'string', 'max:512'],
            'new_upload_tokens' => ['nullable', 'array'],
            'new_upload_tokens.*' => ['string', 'max:128'],
            'gallery_order' => ['nullable', 'array'],
            'gallery_order.*' => ['string', 'max:512'],
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
                    ->with('error', 'You have reached the active listing limit for your '.$activePlan->name.' package.');
            }

            $validated['source'] = 'Agent Dashboard Upload';
            $validated['realtor_profile_id'] = $profile->id;
            $validated['owner_user_id'] = $user->id;
            $validated['listed_by_id'] = $user->id;
        }

        if ($user?->isSeller()) {
            $validated['realtor_profile_id'] = (int) $validated['listing_realtor_profile_id'];
            unset($validated['listing_realtor_profile_id']);
            $validated['owner_user_id'] = $user->id;
            $listingAgent = RealtorProfile::query()->find($validated['realtor_profile_id']);
            $validated['listed_by_id'] = $listingAgent?->user_id;
        }

        [$gallery, $featuredPath] = $this->prepareGalleryPayload($request, null, $validated);

        if ($gallery->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['images' => 'Please upload at least one property image.']);
        }

        $validated['images'] = $gallery->all();
        $validated['image'] = $featuredPath ?? $gallery->first();

        $validated['slug'] = Str::slug($validated['title']).'-'.Str::lower(Str::random(6));
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
        $this->authorize('update', $property);

        return view('pages.dashboards.property-edit', [
            'property' => $property,
            'meta' => [
                'title' => 'Edit Property | OmniReferral',
            ],
        ]);
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->authorize('update', $property);

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
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['string'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:6144'],
            'image' => ['nullable', 'image', 'max:6144'],
            'featured_image' => ['nullable', 'string', 'max:512'],
            'new_upload_tokens' => ['nullable', 'array'],
            'new_upload_tokens.*' => ['string', 'max:128'],
            'gallery_order' => ['nullable', 'array'],
            'gallery_order.*' => ['string', 'max:512'],
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

        [$gallery, $featuredPath] = $this->prepareGalleryPayload($request, $property, $validated);

        if ($gallery->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['images' => 'Keep at least one property image in the gallery.']);
        }

        $validated['images'] = $gallery->all();
        $validated['image'] = $featuredPath ?? $gallery->first();

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
        $this->authorize('delete', $property);

        $property->delete();

        return back()->with('success', 'Property listing removed.');
    }

    public function review(Request $request, Property $property): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        $this->authorize('review', $property);

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

        AdminAudit::log($request, 'property.review.'.$validated['decision'], 'property', $property->id, [
            'title' => $property->title,
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
        abort_unless($property->isApproved() && $property->status === 'Active', 404);

        $deviceId = $request->attributes->get('listing_device_id');
        abort_unless(is_string($deviceId) && $deviceId !== '', 400);

        $favorite = $property->favorites()->where('device_fingerprint', $deviceId)->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', 'Property removed from favorites.');
        }

        $property->favorites()->create([
            'user_id' => $request->user()?->id,
            'device_fingerprint' => $deviceId,
        ]);

        return back()->with('success', 'Property added to favorites.');
    }

    private function prepareGalleryPayload(Request $request, ?Property $property, array $validated): array
    {
        $existingGallery = collect($property?->images ?? [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->values();

        if ($property?->image && ! $existingGallery->contains($property->image)) {
            $existingGallery->prepend($property->image);
        }

        $existingToKeep = collect($validated['existing_images'] ?? [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->values();

        if ($existingToKeep->isNotEmpty()) {
            $existingToKeep = $existingToKeep
                ->filter(fn ($path) => $existingGallery->contains($path))
                ->values();
        } else {
            $existingToKeep = $existingGallery;
        }

        $removeImages = collect($validated['remove_images'] ?? [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->values();

        foreach ($removeImages as $path) {
            $this->deleteImageIfStoredLocally($path);
        }

        $keptExisting = $existingToKeep
            ->reject(fn ($path) => $removeImages->contains($path))
            ->values();

        $newImages = collect();
        $newUploadTokens = collect($validated['new_upload_tokens'] ?? [])
            ->filter(fn ($token) => is_string($token) && trim($token) !== '')
            ->values();

        foreach ($request->file('images', []) as $image) {
            $newImages->push($image->store('properties/listings', 'public'));
        }

        if ($request->hasFile('image')) {
            $newImages->push($request->file('image')->store('properties/listings', 'public'));
            $newUploadTokens->push('legacy::single-image');
        }

        $galleryTokenMap = $keptExisting
            ->mapWithKeys(fn (string $path) => ['existing::'.$path => $path])
            ->merge(
                $newImages->mapWithKeys(fn (string $path, int $index) => [
                    (string) ($newUploadTokens->get($index) ?: 'new::'.$index) => $path,
                ])
            );

        $gallery = $this->applyGalleryOrder(
            $galleryTokenMap,
            collect($validated['gallery_order'] ?? [])
        );

        $featuredPath = $this->resolveFeaturedImagePath(
            (string) ($validated['featured_image'] ?? ''),
            $galleryTokenMap,
            $gallery,
            $property?->image
        );

        return [$gallery, $featuredPath];
    }

    private function applyGalleryOrder(Collection $galleryTokenMap, Collection $galleryOrder): Collection
    {
        $galleryOrder = $galleryOrder
            ->filter(fn ($token) => is_string($token) && trim($token) !== '')
            ->values();

        if ($galleryOrder->isEmpty()) {
            return $galleryTokenMap->values()->unique()->values();
        }

        $ordered = $galleryOrder
            ->map(fn (string $token) => $galleryTokenMap->get($token))
            ->filter(fn ($path) => is_string($path) && trim($path) !== '');

        $remaining = $galleryTokenMap
            ->reject(fn (string $path, string $token) => $galleryOrder->contains($token))
            ->values();

        return $ordered
            ->merge($remaining)
            ->unique()
            ->values();
    }

    private function resolveFeaturedImagePath(string $featuredToken, Collection $galleryTokenMap, Collection $gallery, ?string $currentFeatured): ?string
    {
        $featuredToken = trim($featuredToken);

        if ($featuredToken !== '' && $galleryTokenMap->has($featuredToken)) {
            return $galleryTokenMap->get($featuredToken);
        }

        if ($featuredToken !== '' && $gallery->contains($featuredToken)) {
            return $featuredToken;
        }

        if ($currentFeatured && $gallery->contains($currentFeatured)) {
            return $currentFeatured;
        }

        return $gallery->first();
    }

    private function deleteImageIfStoredLocally(string $path): void
    {
        $path = trim($path);

        if (
            $path === ''
            || Str::startsWith($path, ['http://', 'https://', '/storage/', 'storage/', 'images/'])
            || ! Storage::disk('public')->exists($path)
        ) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    // Property ownership and role rules are enforced via PropertyPolicy.

    protected function ensureAgentProfile(\App\Models\User $user): RealtorProfile
    {
        abort_unless($user->isAgent(), 403);

        return $user->realtorProfile ?: RealtorProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => Str::slug($user->name.'-'.Str::lower(Str::random(6))),
                'brokerage_name' => 'OmniReferral Partner',
                'license_number' => null,
                'address_line_1' => $user->address_line_1,
                'address_line_2' => $user->address_line_2,
                'city' => $user->city ?: 'Dallas',
                'state' => $user->state ?: 'TX',
                'zip_code' => $user->zip_code ?: '75201',
                'specialties' => 'Buyer Representation, Seller Strategy, Lead Conversion',
                'bio' => 'Agent profile created in the OmniReferral workspace.',
                'headshot' => $user->avatar ? 'storage/'.ltrim($user->avatar, '/') : 'images/realtors/3.png',
            ]
        );
    }
}
