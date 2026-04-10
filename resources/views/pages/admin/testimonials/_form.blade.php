@php
    $isEditing = $testimonial->exists;
@endphp

@if ($errors->any())
    <div class="alert alert--error testimonial-admin-alert">
        <strong>Please review the testimonial form.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="testimonial-admin-form">
    <div class="testimonial-admin-form__grid">
        <label>
            <span>Name</span>
            <input type="text" name="name" value="{{ old('name', $testimonial->name) }}" required>
        </label>
        <label>
            <span>Audience</span>
            <select name="audience" required>
                @foreach(['buyer' => 'Buyer', 'seller' => 'Seller', 'agent' => 'Agent', 'community' => 'Community Member'] as $value => $label)
                    <option value="{{ $value }}" {{ old('audience', $testimonial->audience ?: 'agent') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span>Role / Company</span>
            <input type="text" name="company" value="{{ old('company', $testimonial->company) }}" placeholder="e.g. Buyer Client, Seller Client, Team Lead">
        </label>
        <label>
            <span>Location</span>
            <input type="text" name="location" value="{{ old('location', $testimonial->location) }}" placeholder="e.g. Dallas, TX">
        </label>
        <label>
            <span>Rating</span>
            <select name="rating" required>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ (int) old('rating', $testimonial->rating ?: 5) === $i ? 'selected' : '' }}>{{ $i }} stars</option>
                @endfor
            </select>
        </label>
        <label>
            <span>Sort Order</span>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $testimonial->sort_order ?? 0) }}">
        </label>
        <label class="testimonial-admin-form__full">
            <span>Quote</span>
            <textarea name="quote" rows="5" required placeholder="Write the testimonial copy here...">{{ old('quote', $testimonial->quote) }}</textarea>
        </label>
        <label>
            <span>Photo</span>
            <input type="file" name="photo" accept="image/*">
            @if($isEditing && $testimonial->photo)
                <small>Current photo is active.</small>
            @endif
        </label>
        <label>
            <span>Video URL</span>
            <input type="text" name="video_url" value="{{ old('video_url', $testimonial->video_url) }}" placeholder="Paste YouTube, Vimeo, or direct MP4 URL">
        </label>
        <label>
            <span>Upload Video File</span>
            <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime">
            <small>Optional. If uploaded, it will replace the video URL.</small>
        </label>
        <div class="testimonial-admin-form__checks">
            <label class="checkbox">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $testimonial->is_featured) ? 'checked' : '' }}>
                <span>Featured testimonial</span>
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_published" value="1" {{ old('is_published', $testimonial->is_published ?? true) ? 'checked' : '' }}>
                <span>Published on site</span>
            </label>
            @if($isEditing && $testimonial->photo)
                <label class="checkbox">
                    <input type="checkbox" name="remove_photo" value="1">
                    <span>Remove current photo</span>
                </label>
            @endif
            @if($isEditing && $testimonial->video_url)
                <label class="checkbox">
                    <input type="checkbox" name="remove_video" value="1">
                    <span>Remove current video</span>
                </label>
            @endif
        </div>
    </div>

    @if($isEditing)
        <div class="testimonial-admin-preview cockpit-table-card">
            <div class="testimonial-admin-preview__header">
                <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}" loading="lazy">
                <div>
                    <span class="status-pill status-pill--assigned">{{ $testimonial->audience_label }}</span>
                    @if($testimonial->exists)
                        <span class="status-pill status-pill--{{ $testimonial->submissionStatusTone() }}">{{ $testimonial->submissionStatusLabel() }}</span>
                    @endif
                    @if($testimonial->has_video)
                        <span class="status-pill status-pill--qualified">Video</span>
                    @endif
                    <h3>{{ $testimonial->name }}</h3>
                    <p>{{ $testimonial->company ?: 'No role or company set yet' }}</p>
                </div>
            </div>
            <p>"{{ $testimonial->quote }}"</p>
        </div>
    @endif

    <div class="testimonial-admin-form__actions">
        <button type="submit" class="button button--orange">{{ $isEditing ? 'Save Changes' : 'Create Testimonial' }}</button>
        <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Cancel</a>
    </div>
</div>
