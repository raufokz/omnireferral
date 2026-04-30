@php
    $isEdit = $property->exists;
    $existingImages = collect(old('existing_images', $property->images ?? []))
        ->filter(fn ($img) => is_string($img) && $img !== '')
        ->values();

    if ($property->image && ! $existingImages->contains($property->image)) {
        $existingImages->prepend($property->image);
    }

    $listedByType = old('listed_by_type', $property->owner_user_id ? 'user' : 'omnireferral');
@endphp

<div class="workspace-card">
    <span class="eyebrow">Property Details</span>
    <h2>{{ $isEdit ? 'Edit Listing' : 'Create Listing' }}</h2>
    <div class="workspace-form-grid">
        <label class="workspace-field workspace-field--full">
            <span>Title</span>
            <input type="text" name="title" value="{{ old('title', $property->title) }}" required>
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Full Address</span>
            <input type="text" name="location" value="{{ old('location', $property->location) }}" placeholder="Street, city, state" required>
        </label>

        <label class="workspace-field">
            <span>Street</span>
            <input type="text" name="street_address" value="{{ old('street_address', $property->street_address) }}" placeholder="House #, street">
        </label>

        <label class="workspace-field">
            <span>City</span>
            <input type="text" name="city" value="{{ old('city', $property->city) }}" placeholder="City">
        </label>

        <label class="workspace-field">
            <span>State</span>
            <input type="text" name="state" value="{{ old('state', $property->state) }}" placeholder="State/Province">
        </label>

        <label class="workspace-field">
            <span>Country</span>
            <input type="text" name="country" value="{{ old('country', $property->country) }}" placeholder="Country">
        </label>

        <label class="workspace-field">
            <span>ZIP Code</span>
            <input type="text" name="zip_code" value="{{ old('zip_code', $property->zip_code) }}" required>
        </label>

        <label class="workspace-field">
            <span>Property Type</span>
            <select name="property_type" required>
                @foreach(['House', 'Apartment', 'Condo', 'Commercial', 'Land'] as $typeOption)
                    <option value="{{ $typeOption }}" {{ old('property_type', $property->property_type) === $typeOption ? 'selected' : '' }}>{{ $typeOption }}</option>
                @endforeach
            </select>
        </label>

        <label class="workspace-field">
            <span>Price (USD)</span>
            <input type="number" min="0" name="price" value="{{ old('price', $property->price) }}" required>
        </label>

        <label class="workspace-field">
            <span>Price Type</span>
            <select name="price_type" required>
                <option value="sale" {{ old('price_type', $property->price_type ?: 'sale') === 'sale' ? 'selected' : '' }}>Sale</option>
                <option value="rent" {{ old('price_type', $property->price_type) === 'rent' ? 'selected' : '' }}>Rent</option>
            </select>
        </label>

        <label class="workspace-field">
            <span>Status</span>
            <select name="status" required>
                @foreach(['Active', 'Pending', 'Sold', 'Off-Market'] as $statusOption)
                    <option value="{{ $statusOption }}" {{ old('status', $property->status ?: 'Active') === $statusOption ? 'selected' : '' }}>{{ $statusOption }}</option>
                @endforeach
            </select>
        </label>

        <label class="workspace-field">
            <span>Approval</span>
            <select name="approval_status" required>
                @foreach([\App\Models\Property::APPROVAL_PENDING, \App\Models\Property::APPROVAL_APPROVED, \App\Models\Property::APPROVAL_REJECTED] as $approval)
                    <option value="{{ $approval }}" {{ old('approval_status', $property->approval_status ?: \App\Models\Property::APPROVAL_APPROVED) === $approval ? 'selected' : '' }}>
                        {{ ucfirst($approval) }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="workspace-field">
            <span>Beds</span>
            <input type="number" min="0" name="beds" value="{{ old('beds', $property->beds) }}">
        </label>

        <label class="workspace-field">
            <span>Baths</span>
            <input type="number" min="0" step="0.5" name="baths" value="{{ old('baths', $property->baths) }}">
        </label>

        <label class="workspace-field">
            <span>Square Feet</span>
            <input type="number" min="0" name="sqft" value="{{ old('sqft', $property->sqft) }}">
        </label>

        <label class="workspace-field">
            <span>Area Size</span>
            <input type="number" min="0" step="0.01" name="area_size" value="{{ old('area_size', $property->area_size) }}">
        </label>

        <label class="workspace-field">
            <span>Area Unit</span>
            <select name="area_unit">
                @foreach(['sqft' => 'Sq Ft', 'marla' => 'Marla', 'kanal' => 'Kanal'] as $value => $label)
                    <option value="{{ $value }}" {{ old('area_unit', $property->area_unit ?: 'sqft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="workspace-field">
            <span>Year Built</span>
            <input type="number" min="1800" max="2100" name="year_built" value="{{ old('year_built', $property->year_built) }}">
        </label>

        <label class="workspace-field">
            <span>Parking Spaces</span>
            <input type="number" min="0" max="50" name="parking_spaces" value="{{ old('parking_spaces', $property->parking_spaces) }}">
        </label>

        <label class="workspace-field">
            <span>Garage Spaces</span>
            <input type="number" min="0" max="50" name="garage_spaces" value="{{ old('garage_spaces', $property->garage_spaces) }}">
        </label>

        <label class="workspace-field">
            <span>Furnishing</span>
            <select name="furnishing_status">
                <option value="">Select</option>
                <option value="furnished" {{ old('furnishing_status', $property->furnishing_status) === 'furnished' ? 'selected' : '' }}>Furnished</option>
                <option value="semi_furnished" {{ old('furnishing_status', $property->furnishing_status) === 'semi_furnished' ? 'selected' : '' }}>Semi Furnished</option>
                <option value="unfurnished" {{ old('furnishing_status', $property->furnishing_status) === 'unfurnished' ? 'selected' : '' }}>Unfurnished</option>
            </select>
        </label>

        <label class="workspace-field">
            <span>Condition</span>
            <select name="property_condition">
                <option value="">Select</option>
                <option value="new" {{ old('property_condition', $property->property_condition) === 'new' ? 'selected' : '' }}>New</option>
                <option value="old" {{ old('property_condition', $property->property_condition) === 'old' ? 'selected' : '' }}>Old</option>
                <option value="renovated" {{ old('property_condition', $property->property_condition) === 'renovated' ? 'selected' : '' }}>Renovated</option>
            </select>
        </label>

        <label class="workspace-field">
            <span>Source</span>
            <input type="text" name="source" value="{{ old('source', $property->source ?: 'OmniReferral Admin Listing') }}">
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Description (Rich Text / HTML Allowed)</span>
            <textarea name="description" rows="6" placeholder="Add a detailed Zillow-style listing description...">{{ old('description', $property->description) }}</textarea>
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Neighborhood Information</span>
            <textarea name="neighborhood_info" rows="4" placeholder="Schools, commute, hospitals, markets, mosque distance...">{{ old('neighborhood_info', $property->neighborhood_info) }}</textarea>
        </label>

        <label class="workspace-field workspace-field--full">
            <span>Location Highlights / Walkability Notes</span>
            <textarea name="location_highlights" rows="3" placeholder="Parks, transport, walk score context...">{{ old('location_highlights', $property->location_highlights) }}</textarea>
        </label>

        <label class="workspace-field">
            <span>Walk Score (0-100)</span>
            <input type="number" min="0" max="100" name="walk_score" value="{{ old('walk_score', $property->walk_score) }}">
        </label>
    </div>
</div>

@if(!empty($canManageListedBy))
    <div class="workspace-card">
        <span class="eyebrow">Ownership</span>
        <h2>Listed By</h2>
        <div class="workspace-form-grid">
            <label class="workspace-field">
                <span>Listing Owner Type</span>
                <select name="listed_by_type" data-listed-by-type>
                    <option value="omnireferral" {{ $listedByType === 'omnireferral' ? 'selected' : '' }}>OmniReferral/Admin</option>
                    <option value="user" {{ $listedByType === 'user' ? 'selected' : '' }}>Specific User</option>
                </select>
            </label>
            <label class="workspace-field workspace-field--full" data-listed-by-user-wrap style="{{ $listedByType === 'user' ? '' : 'display:none;' }}">
                <span>User</span>
                <select name="listed_by_user_id">
                    <option value="">Select user...</option>
                    @foreach($listingUsers as $user)
                        <option value="{{ $user->id }}" {{ (int) old('listed_by_user_id', $property->owner_user_id) === (int) $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ ucfirst($user->role) }}) - {{ $user->email }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>
@endif

<div class="workspace-card">
    <span class="eyebrow">Amenities</span>
    <h2>Zillow-Style Amenities And Local Essentials</h2>
    @php
        $selectedAmenities = collect(old('amenities', $property->amenities ?? []))->values()->all();
        $amenityOptions = [
            'electricity' => 'Electricity',
            'gas' => 'Gas',
            'water' => 'Water',
            'nearby_schools' => 'Nearby Schools',
            'hospitals' => 'Hospitals',
            'markets' => 'Markets',
            'mosques' => 'Mosques',
        ];
    @endphp
    <div class="workspace-grid workspace-grid--4">
        @foreach($amenityOptions as $amenityKey => $amenityLabel)
            <label class="workspace-card" style="padding: 0.75rem;">
                <span style="font-weight: 600; font-size: 0.9rem;">{{ $amenityLabel }}</span>
                <input type="checkbox" name="amenities[]" value="{{ $amenityKey }}" {{ in_array($amenityKey, $selectedAmenities, true) ? 'checked' : '' }}>
            </label>
        @endforeach
    </div>
</div>

<div class="workspace-card">
    <span class="eyebrow">Media</span>
    <h2>Gallery, Video, 360°</h2>

    @if($existingImages->isNotEmpty())
        <div class="workspace-grid workspace-grid--4" style="margin-bottom: 0.9rem;">
            @foreach($existingImages as $imagePath)
                @php
                    $imageUrl = \Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://', '/storage/'])
                        ? $imagePath
                        : (\Illuminate\Support\Str::startsWith($imagePath, 'storage/')
                            ? '/' . $imagePath
                            : asset('storage/' . ltrim($imagePath, '/')));
                @endphp
                <article class="workspace-card" style="padding: 0.65rem;">
                    <img src="{{ $imageUrl }}" alt="Property image" style="width: 100%; height: 120px; object-fit: cover; border-radius: 12px;">
                    <label class="workspace-field" style="margin-top: 0.5rem;">
                        <span style="font-size: 0.8rem;">Remove image</span>
                        <input type="checkbox" name="remove_images[]" value="{{ $imagePath }}">
                    </label>
                </article>
            @endforeach
        </div>
    @endif

    <div class="workspace-form-grid">
        <label class="workspace-field workspace-field--full">
            <span>{{ $isEdit ? 'Add More Images' : 'Upload Images' }} (up to 10)</span>
            <input type="file" name="images[]" accept="image/*" multiple {{ $isEdit ? '' : 'required' }}>
        </label>
        <label class="workspace-field workspace-field--full">
            <span>Video Tour URL (optional)</span>
            <input type="url" name="video_tour_url" value="{{ old('video_tour_url', $property->video_tour_url) }}" placeholder="https://youtube.com/...">
        </label>
        <label class="workspace-field workspace-field--full">
            <span>360° View URL (optional)</span>
            <input type="url" name="view_360_url" value="{{ old('view_360_url', $property->view_360_url) }}" placeholder="https://...">
        </label>
        <label class="workspace-field">
            <span>Featured Listing</span>
            <select name="is_featured">
                <option value="0" {{ (int) old('is_featured', $property->is_featured ? 1 : 0) === 0 ? 'selected' : '' }}>No</option>
                <option value="1" {{ (int) old('is_featured', $property->is_featured ? 1 : 0) === 1 ? 'selected' : '' }}>Yes</option>
            </select>
        </label>
    </div>
</div>

<div class="workspace-actions">
    <button type="submit" class="button">{{ $isEdit ? 'Save Changes' : 'Create Listing' }}</button>
    <a href="{{ route('admin.properties.index') }}" class="button button--ghost-blue">Cancel</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.querySelector('[data-listed-by-type]');
    const userWrap = document.querySelector('[data-listed-by-user-wrap]');
    if (!typeSelect || !userWrap) {
        return;
    }

    const toggle = function () {
        userWrap.style.display = typeSelect.value === 'user' ? '' : 'none';
    };

    typeSelect.addEventListener('change', toggle);
    toggle();
});
</script>
@endpush
