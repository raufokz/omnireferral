@php
    $isEditing = $testimonial->exists;
@endphp

@if ($errors->any())
    <div class="workspace-empty" style="margin-bottom: 0.8rem; text-align: left;">
        <strong>Please review the testimonial form:</strong>
        <ul style="margin: 0.5rem 0 0 1rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="workspace-form-grid">
    <label class="workspace-field">
        <span>Name</span>
        <input type="text" name="name" value="{{ old('name', $testimonial->name) }}" required>
    </label>
    <label class="workspace-field">
        <span>Audience</span>
        <select name="audience" required>
            @foreach(['buyer' => 'Buyer', 'seller' => 'Seller', 'agent' => 'Agent', 'community' => 'Community Member'] as $value => $label)
                <option value="{{ $value }}" {{ old('audience', $testimonial->audience ?: 'agent') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label class="workspace-field">
        <span>Role / Company</span>
        <input type="text" name="company" value="{{ old('company', $testimonial->company) }}" placeholder="e.g. Buyer Client">
    </label>
    <label class="workspace-field">
        <span>Location</span>
        <input type="text" name="location" value="{{ old('location', $testimonial->location) }}" placeholder="e.g. Dallas, TX">
    </label>
    <label class="workspace-field">
        <span>Rating</span>
        <select name="rating" required>
            @for($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" {{ (int) old('rating', $testimonial->rating ?: 5) === $i ? 'selected' : '' }}>{{ $i }} stars</option>
            @endfor
        </select>
    </label>
    <label class="workspace-field">
        <span>Sort Order</span>
        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}">
    </label>
    <label class="workspace-field workspace-field--full">
        <span>Quote</span>
        <textarea name="quote" rows="5" required>{{ old('quote', $testimonial->quote) }}</textarea>
    </label>
    <label class="workspace-field">
        <span>Photo</span>
        <input type="file" name="photo" accept="image/*">
        @if($isEditing && $testimonial->photo)
            <small class="workspace-property__meta">Current photo is active.</small>
        @endif
    </label>
    <label class="workspace-field">
        <span>Video URL</span>
        <input type="text" name="video_url" value="{{ old('video_url', $testimonial->video_url) }}" placeholder="YouTube, Vimeo, or direct URL">
    </label>
    <label class="workspace-field workspace-field--full">
        <span>Upload Video File</span>
        <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
        <small class="workspace-property__meta">Optional. Uploading a file replaces the video URL.</small>
    </label>
</div>

<div class="workspace-actions" style="margin-top: 0.85rem;">
    <label class="workspace-pill">
        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $testimonial->is_featured) ? 'checked' : '' }} style="margin-right: 0.4rem;">
        Featured testimonial
    </label>
    <label class="workspace-pill">
        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $testimonial->is_published ?? true) ? 'checked' : '' }} style="margin-right: 0.4rem;">
        Published on site
    </label>
    @if($isEditing && $testimonial->photo)
        <label class="workspace-pill workspace-pill--accent">
            <input type="checkbox" name="remove_photo" value="1" style="margin-right: 0.4rem;">
            Remove photo
        </label>
    @endif
    @if($isEditing && $testimonial->video_url)
        <label class="workspace-pill workspace-pill--accent">
            <input type="checkbox" name="remove_video" value="1" style="margin-right: 0.4rem;">
            Remove video
        </label>
    @endif
</div>

@if($isEditing)
    <section class="workspace-card" style="margin-top: 0.9rem;">
        <span class="eyebrow">Preview</span>
        <h3>{{ $testimonial->name }}</h3>
        <div class="workspace-pill-row">
            <span class="workspace-pill">{{ $testimonial->audience_label }}</span>
            <span class="workspace-pill">{{ $testimonial->submissionStatusLabel() }}</span>
            @if($testimonial->has_video)
                <span class="workspace-pill workspace-pill--accent">Video</span>
            @endif
        </div>
        <p style="margin-top: 0.7rem;">"{{ $testimonial->quote }}"</p>
    </section>
@endif

<div class="workspace-actions" style="margin-top: 0.95rem;">
    <button type="submit" class="button">{{ $isEditing ? 'Save Changes' : 'Create Testimonial' }}</button>
    <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Cancel</a>
</div>
