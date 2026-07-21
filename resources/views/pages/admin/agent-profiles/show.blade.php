@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Staff Workspace')
@section('dashboard_title', $user?->publicDisplayName() ?: 'Edit Agent Profile')
@section('dashboard_description', 'Update directory data, change status, or mark as Featured.')

@section('dashboard_actions')
    <a href="{{ route('admin.agent-profiles.index') }}" class="button button--ghost-blue">All profiles</a>
    @if($profile->isPublicVisible())
        <a href="{{ route('agents.profile', $profile) }}" class="button" target="_blank" rel="noopener">Public SEO page</a>
    @endif
    @if($profile->profile_status !== 'featured')
        <form method="POST" action="{{ route('admin.agent-profiles.feature', $profile) }}" style="display:inline;">@csrf<button type="submit" class="button button--orange">Mark Featured</button></form>
    @endif
    @if($profile->profile_status !== 'published')
        <form method="POST" action="{{ route('admin.agent-profiles.publish', $profile) }}" style="display:inline;">@csrf<button type="submit" class="button button--ghost-blue">Approve</button></form>
    @endif
    @if($profile->profile_status !== 'suspended')
        <form method="POST" action="{{ route('admin.agent-profiles.suspend', $profile) }}" style="display:inline;">@csrf<button type="submit" class="button button--ghost-blue">Suspend</button></form>
    @endif
@endsection

@section('content')
@php
    $socialLinks = is_array($profile->social_links) ? $profile->social_links : [];
    $subscription = $user?->activeAgentSubscription;
    $subPackage = $subscription?->package;
    $currentPlan = $user?->currentPlan;

    $resolvedPlanName = $subPackage?->displayName() ?? $currentPlan?->displayName() ?? null;
    $resolvedPlanSlug = $subPackage?->slug ?? $currentPlan?->slug ?? null;
    $planBadgeClass = match ($resolvedPlanSlug) {
        'starter-leads', 'quick-leads' => 'agent-admin__plan--starter',
        'growth-leads', 'power-leads' => 'agent-admin__plan--growth',
        'elite-leads', 'prime-leads' => 'agent-admin__plan--elite',
        default => 'agent-admin__plan--none',
    };
    $subStatusLabel = match (true) {
        ! $subscription => 'No Subscription',
        $subscription->is_active && $subscription->payment_status === 'paid' => 'Active',
        $subscription->ends_at?->isPast() => 'Expired',
        default => ucfirst($subscription->payment_status ?: 'Pending'),
    };
@endphp
<div class="workspace-stack">
    @if(session('success'))<div class="workspace-card">{{ session('success') }}</div>@endif

    <section class="workspace-card">
        <h3 style="margin:0 0 1rem; font-size:1rem; font-weight:700;">Subscription Information</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
            <div>
                <span style="display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.03em;">Current Plan</span>
                @if($resolvedPlanName)
                    <span class="agent-admin__plan {{ $planBadgeClass }}">{{ $resolvedPlanName }}</span>
                @else
                    <span class="agent-admin__plan agent-admin__plan--none">No Plan</span>
                @endif
            </div>
            <div>
                <span style="display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.03em;">Subscription Status</span>
                <strong style="font-size:0.875rem;">{{ $subStatusLabel }}</strong>
            </div>
            @if($subscription)
                <div>
                    <span style="display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.03em;">Payment Status</span>
                    <strong style="font-size:0.875rem;">{{ ucfirst($subscription->payment_status ?: 'Unknown') }}</strong>
                </div>
                <div>
                    <span style="display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.03em;">Start Date</span>
                    <strong style="font-size:0.875rem;">{{ $subscription->starts_at?->format('M j, Y') ?? '-' }}</strong>
                </div>
                <div>
                    <span style="display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.03em;">End Date</span>
                    <strong style="font-size:0.875rem;">{{ $subscription->ends_at?->format('M j, Y') ?? '-' }}</strong>
                </div>
            @endif
            @if($subPackage?->monthly_lead_quota)
                <div>
                    <span style="display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.03em;">Monthly Lead Quota</span>
                    <strong style="font-size:0.875rem;">{{ $subPackage->monthly_lead_quota }}/mo</strong>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.agent-profiles.change-plan', $profile) }}" style="margin-top:1.25rem; border-top:1px solid var(--agent-admin-line, #e2e8f0); padding-top:1rem; display:flex; align-items:flex-end; gap:1rem; flex-wrap:wrap;">
            @csrf
            <label style="display:flex; flex-direction:column; gap:0.35rem; min-width:220px;">
                <span style="font-size:0.7rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.03em;">Change Plan</span>
                <select name="package_id" required style="min-height:2.4rem; border:1px solid #e2e8f0; border-radius:8px; padding:0.4rem 0.6rem; font-size:0.85rem;">
                    @foreach($availablePlans as $planOption)
                        <option value="{{ $planOption->id }}" @selected($subPackage?->id === $planOption->id)>
                            {{ $planOption->displayName() }} @if($subPackage?->id === $planOption->id)(current)@endif
                        </option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="button button--orange" onclick="return confirm('This will deactivate the current subscription and activate the new plan. Continue?')" style="min-height:2.4rem;">Change Plan</button>
        </form>
    </section>

    <section class="workspace-card">
        <form method="POST" action="{{ route('admin.agent-profiles.update', $profile) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="workspace-form-grid">
                <label class="workspace-field"><span>Name</span><input type="text" name="name" value="{{ old('name', $user?->name) }}" required></label>
                <label class="workspace-field"><span>Display name</span><input type="text" name="display_name" value="{{ old('display_name', $user?->display_name) }}"></label>
                <label class="workspace-field"><span>Internal email</span><input type="email" name="email" value="{{ old('email', $user?->email) }}"></label>
                <label class="workspace-field"><span>Internal phone</span><input type="text" name="phone" value="{{ old('phone', $user?->phone) }}"></label>
                <label class="workspace-field"><span>Brokerage</span><input type="text" name="brokerage_name" value="{{ old('brokerage_name', $profile->brokerage_name) }}" required></label>
                <label class="workspace-field"><span>License</span><input type="text" name="license_number" value="{{ old('license_number', $profile->license_number) }}"></label>
                <label class="workspace-field"><span>Service city</span><input type="text" name="service_city" value="{{ old('service_city', $profile->service_city) }}" required></label>
                <label class="workspace-field"><span>Service state</span><input type="text" name="service_state" value="{{ old('service_state', $profile->service_state) }}" maxlength="2" required></label>
                <label class="workspace-field"><span>ZIP</span><input type="text" name="service_zip_code" value="{{ old('service_zip_code', $profile->service_zip_code) }}"></label>
                <label class="workspace-field"><span>Years experience</span><input type="number" name="years_of_experience" value="{{ old('years_of_experience', $profile->years_of_experience) }}" min="0" max="60"></label>
                <label class="workspace-field"><span>Languages</span><input type="text" name="languages" value="{{ old('languages', $profile->languages) }}"></label>
                <label class="workspace-field"><span>Rating</span><input type="number" step="0.1" name="rating" value="{{ old('rating', $profile->rating) }}" min="0" max="5"></label>
                <label class="workspace-field"><span>Reviews</span><input type="number" name="review_count" value="{{ old('review_count', $profile->review_count) }}" min="0"></label>
                <label class="workspace-field"><span>Leads closed</span><input type="number" name="leads_closed" value="{{ old('leads_closed', $profile->leads_closed) }}" min="0"></label>
                <label class="workspace-field"><span>Status</span>
                    <select name="profile_status" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('profile_status', $profile->profile_status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field workspace-field--full"><span>Source URL</span><input type="url" name="source_url" value="{{ old('source_url', $profile->source_url) }}"></label>
                <label class="workspace-field workspace-field--full"><span>Specialties</span><input type="text" name="specialties_text" value="{{ old('specialties_text', $profile->specialties) }}"></label>
                <label class="workspace-field workspace-field--full"><span>Market areas</span><input type="text" name="market_areas" value="{{ old('market_areas', $profile->market_areas) }}"></label>
                <label class="workspace-field"><span>Website</span><input type="url" name="website_url" value="{{ old('website_url', $socialLinks['website'] ?? '') }}"></label>
                <label class="workspace-field"><span>LinkedIn</span><input type="url" name="social_linkedin_url" value="{{ old('social_linkedin_url', $socialLinks['linkedin'] ?? '') }}"></label>
                <label class="workspace-field"><span>Facebook</span><input type="url" name="social_facebook_url" value="{{ old('social_facebook_url', $socialLinks['facebook'] ?? '') }}"></label>
                <label class="workspace-field"><span>Instagram</span><input type="url" name="social_instagram_url" value="{{ old('social_instagram_url', $socialLinks['instagram'] ?? '') }}"></label>
                <label class="workspace-field workspace-field--full"><span>Bio</span><textarea name="bio" rows="5" required>{{ old('bio', $profile->bio) }}</textarea></label>
                <label class="workspace-field"><span>New headshot</span><input type="file" name="headshot" accept="image/*"></label>
                <label class="workspace-field"><span>Headshot URL</span><input type="url" name="headshot_url" value="{{ old('headshot_url') }}"></label>
            </div>
            <button type="submit" class="button button--orange" style="margin-top:1rem;">Save changes</button>
        </form>
    </section>
</div>
@endsection
