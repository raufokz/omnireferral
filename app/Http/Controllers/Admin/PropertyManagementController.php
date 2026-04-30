<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyManagementController extends Controller
{
    public function index(Request $request): View
    {
        $workspaceUser = $request->user();
        abort_unless($workspaceUser && $workspaceUser->isStaff(), 403);

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => (string) $request->query('status', ''),
            'approval_status' => (string) $request->query('approval_status', ''),
            'property_type' => (string) $request->query('property_type', ''),
            'listed_by' => (string) $request->query('listed_by', ''),
            'price_min' => (string) $request->query('price_min', ''),
            'price_max' => (string) $request->query('price_max', ''),
            'beds' => (string) $request->query('beds', ''),
            'baths' => (string) $request->query('baths', ''),
            'area_min' => (string) $request->query('area_min', ''),
            'area_max' => (string) $request->query('area_max', ''),
            'sort' => (string) $request->query('sort', 'latest'),
        ];

        $query = Property::query()
            ->with(['owner', 'realtorProfile.user', 'reviewedBy']);

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('zip_code', 'like', '%' . $search . '%')
                    ->orWhere('source', 'like', '%' . $search . '%');
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['approval_status'] !== '') {
            $query->where('approval_status', $filters['approval_status']);
        }

        if ($filters['property_type'] !== '') {
            $query->where('property_type', $filters['property_type']);
        }

        if ($filters['listed_by'] === 'omnireferral') {
            $query->whereNull('owner_user_id');
        } elseif ($filters['listed_by'] === 'user') {
            $query->whereNotNull('owner_user_id');
        }

        if (is_numeric($filters['price_min'])) {
            $query->where('price', '>=', (int) $filters['price_min']);
        }

        if (is_numeric($filters['price_max'])) {
            $query->where('price', '<=', (int) $filters['price_max']);
        }

        if (is_numeric($filters['beds'])) {
            $query->where('beds', '>=', (int) $filters['beds']);
        }

        if (is_numeric($filters['baths'])) {
            $query->where('baths', '>=', (float) $filters['baths']);
        }

        if (is_numeric($filters['area_min'])) {
            $query->where(function ($builder) use ($filters) {
                $builder->where('area_size', '>=', (float) $filters['area_min'])
                    ->orWhere('sqft', '>=', (int) $filters['area_min']);
            });
        }

        if (is_numeric($filters['area_max'])) {
            $query->where(function ($builder) use ($filters) {
                $builder->where('area_size', '<=', (float) $filters['area_max'])
                    ->orWhere('sqft', '<=', (int) $filters['area_max']);
            });
        }

        match ($filters['sort']) {
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        $properties = $query
            ->paginate(15)
            ->withQueryString();

        return view('pages/admin/properties/index', [
            'properties' => $properties,
            'filters' => $filters,
            'statuses' => Property::query()->select('status')->distinct()->orderBy('status')->pluck('status'),
            'approvalStatuses' => collect([
                Property::APPROVAL_PENDING,
                Property::APPROVAL_APPROVED,
                Property::APPROVAL_REJECTED,
            ]),
            'propertyTypes' => Property::query()->select('property_type')->distinct()->orderBy('property_type')->pluck('property_type'),
            'summary' => [
                'total' => Property::count(),
                'active' => Property::where('status', 'Active')->count(),
                'pendingReview' => Property::pendingReview()->count(),
                'userListed' => Property::whereNotNull('owner_user_id')->count(),
            ],
            'mapPins' => $properties->map(function (Property $property) {
                return [
                    'title' => $property->title,
                    'address' => $property->fullAddress(),
                    'url' => route('admin.properties.edit', $property),
                ];
            }),
            'canCreate' => $workspaceUser->isAdmin(),
            'canDelete' => $workspaceUser->isAdmin(),
            'isStaffView' => $workspaceUser->role === 'staff',
            'meta' => [
                'title' => 'Property Registry | OmniReferral',
                'description' => 'Manage marketplace listings, media, ownership, and moderation in one responsive admin module.',
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $workspaceUser = $request->user();
        abort_unless($workspaceUser && $workspaceUser->isAdmin(), 403);

        return view('pages/admin/properties/create', [
            'property' => new Property([
                'status' => 'Active',
                'approval_status' => Property::APPROVAL_APPROVED,
            ]),
            'listingUsers' => $this->listingUsers(),
            'meta' => [
                'title' => 'Create Property Listing | OmniReferral',
                'description' => 'Create a new admin listing with full address, ZIP, media, and ownership details.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workspaceUser = $request->user();
        abort_unless($workspaceUser && $workspaceUser->isAdmin(), 403);

        $validated = $this->validatePayload($request, true);
        $prepared = $this->preparePayload($validated, $request, null);

        Property::create($prepared);

        return redirect()
            ->route('admin.properties.index')
            ->with('success', 'Property listing created successfully.');
    }

    public function edit(Request $request, Property $property): View
    {
        $workspaceUser = $request->user();
        abort_unless($workspaceUser && $workspaceUser->isStaff(), 403);

        return view('pages/admin/properties/edit', [
            'property' => $property,
            'listingUsers' => $workspaceUser->isAdmin() ? $this->listingUsers() : collect(),
            'canDelete' => $workspaceUser->isAdmin(),
            'canManageListedBy' => $workspaceUser->isAdmin(),
            'meta' => [
                'title' => 'Edit Property Listing | OmniReferral',
                'description' => 'Update listing details, media gallery, address data, and moderation state.',
            ],
        ]);
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $workspaceUser = $request->user();
        abort_unless($workspaceUser && $workspaceUser->isStaff(), 403);

        $validated = $this->validatePayload($request, false, $workspaceUser->isAdmin());
        $prepared = $this->preparePayload($validated, $request, $property, $workspaceUser->isAdmin());

        $property->update($prepared);

        return redirect()
            ->route('admin.properties.index')
            ->with('success', 'Property listing updated successfully.');
    }

    public function destroy(Request $request, Property $property): RedirectResponse
    {
        $workspaceUser = $request->user();
        abort_unless($workspaceUser && $workspaceUser->isAdmin(), 403);

        $existingImages = collect($property->images ?? [])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values()
            ->all();
        if ($property->image && ! in_array($property->image, $existingImages, true)) {
            $existingImages[] = $property->image;
        }

        foreach ($existingImages as $path) {
            $this->deleteImageIfStoredLocally($path);
        }

        $property->delete();

        return redirect()
            ->route('admin.properties.index')
            ->with('success', 'Property listing deleted.');
    }

    protected function validatePayload(Request $request, bool $isCreate, bool $canManageListedBy = true): array
    {
        $statusOptions = ['Active', 'Pending', 'Sold', 'Off-Market'];
        $base = [
            'title' => ['required', 'string', 'max:255'],
            'property_type' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'price_type' => ['required', Rule::in(['sale', 'rent'])],
            'location' => ['required', 'string', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'zip_code' => ['required', 'string', 'max:10'],
            'beds' => ['nullable', 'integer', 'min:0'],
            'baths' => ['nullable', 'numeric', 'min:0'],
            'sqft' => ['nullable', 'integer', 'min:0'],
            'area_size' => ['nullable', 'numeric', 'min:0'],
            'area_unit' => ['nullable', Rule::in(['sqft', 'marla', 'kanal'])],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'parking_spaces' => ['nullable', 'integer', 'min:0', 'max:50'],
            'garage_spaces' => ['nullable', 'integer', 'min:0', 'max:50'],
            'furnishing_status' => ['nullable', Rule::in(['furnished', 'semi_furnished', 'unfurnished'])],
            'property_condition' => ['nullable', Rule::in(['new', 'old', 'renovated'])],
            'description' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:120'],
            'video_tour_url' => ['nullable', 'url', 'max:255'],
            'view_360_url' => ['nullable', 'url', 'max:255'],
            'neighborhood_info' => ['nullable', 'string'],
            'walk_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'location_highlights' => ['nullable', 'string'],
            'status' => ['required', Rule::in($statusOptions)],
            'approval_status' => ['required', Rule::in([
                Property::APPROVAL_PENDING,
                Property::APPROVAL_APPROVED,
                Property::APPROVAL_REJECTED,
            ])],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', Rule::in([
                'electricity',
                'gas',
                'water',
                'nearby_schools',
                'hospitals',
                'markets',
                'mosques',
            ])],
            'is_featured' => ['nullable', 'boolean'],
            'images' => [$isCreate ? 'required' : 'nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:6144'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string'],
        ];

        if ($canManageListedBy) {
            $base['listed_by_type'] = ['required', Rule::in(['omnireferral', 'user'])];
            $base['listed_by_user_id'] = ['nullable', 'required_if:listed_by_type,user', 'integer', 'exists:users,id'];
        }

        $validated = $request->validate($base);

        return $validated;
    }

    protected function preparePayload(array $validated, Request $request, ?Property $property = null, bool $canManageListedBy = true): array
    {
        $existingImages = collect($property?->images ?? [])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values();

        if ($property?->image && ! $existingImages->contains($property->image)) {
            $existingImages->push($property->image);
        }

        $removeImages = collect($validated['remove_images'] ?? [])
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->values();

        foreach ($removeImages as $path) {
            $this->deleteImageIfStoredLocally($path);
        }

        $keptImages = $existingImages
            ->reject(fn ($path) => $removeImages->contains($path))
            ->values();

        $newImages = collect();
        foreach ($request->file('images', []) as $image) {
            $newImages->push($image->store('properties/listings', 'public'));
        }

        $gallery = $keptImages
            ->merge($newImages)
            ->unique()
            ->take(10)
            ->values();

        $payload = [
            'title' => $validated['title'],
            'property_type' => $validated['property_type'],
            'price' => $validated['price'],
            'price_type' => $validated['price_type'],
            'location' => $validated['location'],
            'street_address' => $validated['street_address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
            'zip_code' => $validated['zip_code'],
            'beds' => $validated['beds'] ?? 0,
            'baths' => $validated['baths'] ?? 0,
            'sqft' => $validated['sqft'] ?? 0,
            'area_size' => $validated['area_size'] ?? null,
            'area_unit' => $validated['area_unit'] ?? 'sqft',
            'year_built' => $validated['year_built'] ?? null,
            'parking_spaces' => $validated['parking_spaces'] ?? null,
            'garage_spaces' => $validated['garage_spaces'] ?? null,
            'furnishing_status' => $validated['furnishing_status'] ?? null,
            'property_condition' => $validated['property_condition'] ?? null,
            'description' => $validated['description'] ?? null,
            'video_tour_url' => $validated['video_tour_url'] ?? null,
            'view_360_url' => $validated['view_360_url'] ?? null,
            'amenities' => array_values($validated['amenities'] ?? []),
            'neighborhood_info' => $validated['neighborhood_info'] ?? null,
            'walk_score' => $validated['walk_score'] ?? null,
            'location_highlights' => $validated['location_highlights'] ?? null,
            'source' => $validated['source'] ?? 'OmniReferral Admin Listing',
            'status' => $validated['status'],
            'approval_status' => $validated['approval_status'],
            'approval_notes' => $validated['approval_status'] === Property::APPROVAL_REJECTED
                ? ($property?->approval_notes ?? 'Rejected from admin listing registry.')
                : null,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'image' => $gallery->first(),
            'images' => $gallery->all(),
            'published_at' => $validated['approval_status'] === Property::APPROVAL_APPROVED ? ($property?->published_at ?? now()) : null,
            'reviewed_by_user_id' => $request->user()?->id,
            'reviewed_at' => now(),
        ];

        if ($canManageListedBy) {
            if (($validated['listed_by_type'] ?? 'omnireferral') === 'user') {
                $payload['owner_user_id'] = (int) $validated['listed_by_user_id'];
            } else {
                $payload['owner_user_id'] = null;
            }
        }

        if (! $property) {
            $payload['slug'] = Str::slug($validated['title']) . '-' . Str::lower(Str::random(6));
        }

        return $payload;
    }

    protected function listingUsers()
    {
        return User::query()
            ->whereIn('role', ['buyer', 'seller', 'agent', 'staff', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    protected function deleteImageIfStoredLocally(string $path): void
    {
        if (
            Str::startsWith($path, ['http://', 'https://', '/storage/', 'storage/', 'images/'])
            || ! Storage::disk('public')->exists($path)
        ) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
