@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Staff Workspace')
@section('dashboard_title', 'Add Agent Profile')
@section('dashboard_description', 'Create a directory profile from a public source (Zillow, Realtor.com, LinkedIn, brokerage site, etc.).')

@section('content')
<div class="workspace-card">
    @if($errors->any())
        <ul class="workspace-empty" style="color:#b42318;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('admin.agent-profiles.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="workspace-form-grid">
            <label class="workspace-field"><span>Agent name *</span><input type="text" name="name" value="{{ old('name') }}" required></label>
            <label class="workspace-field"><span>Display name</span><input type="text" name="display_name" value="{{ old('display_name') }}"></label>
            <label class="workspace-field"><span>Email (internal, optional)</span><input type="email" name="email" value="{{ old('email') }}" placeholder="Auto-generated if blank"></label>
            <label class="workspace-field"><span>Phone (internal, optional)</span><input type="text" name="phone" value="{{ old('phone') }}"></label>
            <label class="workspace-field"><span>Brokerage *</span><input type="text" name="brokerage_name" value="{{ old('brokerage_name') }}" required></label>
            <label class="workspace-field"><span>License #</span><input type="text" name="license_number" value="{{ old('license_number') }}"></label>
            <label class="workspace-field"><span>Service city *</span><input type="text" name="service_city" value="{{ old('service_city') }}" required></label>
            <label class="workspace-field"><span>Service state *</span><input type="text" name="service_state" value="{{ old('service_state') }}" maxlength="2" required></label>
            <label class="workspace-field"><span>ZIP</span><input type="text" name="service_zip_code" value="{{ old('service_zip_code') }}"></label>
            <label class="workspace-field"><span>Years experience</span><input type="number" name="years_of_experience" min="0" max="60" value="{{ old('years_of_experience') }}"></label>
            <label class="workspace-field"><span>Languages</span><input type="text" name="languages" value="{{ old('languages') }}"></label>
            <label class="workspace-field"><span>Rating</span><input type="number" step="0.1" min="0" max="5" name="rating" value="{{ old('rating', 4.5) }}"></label>
            <label class="workspace-field"><span>Review count</span><input type="number" min="0" name="review_count" value="{{ old('review_count', 0) }}"></label>
            <label class="workspace-field"><span>Profile status *</span>
                <select name="profile_status" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('profile_status', 'published') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="workspace-field workspace-field--full"><span>Source URL</span><input type="url" name="source_url" value="{{ old('source_url') }}" placeholder="https://zillow.com/..."></label>
            <label class="workspace-field workspace-field--full"><span>Specialties (comma separated)</span><input type="text" name="specialties_text" value="{{ old('specialties_text') }}"></label>
            <label class="workspace-field workspace-field--full"><span>Market areas</span><input type="text" name="market_areas" value="{{ old('market_areas') }}"></label>
            <label class="workspace-field workspace-field--full"><span>Bio *</span><textarea name="bio" rows="5" required minlength="40">{{ old('bio') }}</textarea></label>
            <label class="workspace-field"><span>Headshot upload</span><input type="file" name="headshot" accept="image/*"></label>
            <label class="workspace-field"><span>Headshot URL</span><input type="url" name="headshot_url" value="{{ old('headshot_url') }}"></label>
        </div>
        <button type="submit" class="button button--orange" style="margin-top:1rem;">Create profile</button>
    </form>
</div>
@endsection
