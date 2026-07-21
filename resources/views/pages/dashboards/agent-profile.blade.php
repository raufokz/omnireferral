@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Profile Settings')
@section('dashboard_description', 'Keep your profile sharp — brokerage, specialties, bio, and headshot all affect how your profile ranks and converts in the directory.')

@section('dashboard_actions')
    @if($agentProfile->isPublicVisible())
        <a href="{{ route('agents.profile', $agentProfile) }}" class="button button--ghost-blue" target="_blank" rel="noopener">View Public Profile</a>
    @endif
    <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Overview</a>
@endsection

@push('styles')
<style>
.profile-completeness-bar { height: 10px; border-radius: 999px; background: #e8edf4; overflow: hidden; }
.profile-completeness-bar__fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #0b3668, #ff6b00); transition: width 0.6s cubic-bezier(.16,1,.3,1); }

.profile-form-section {
    border-top: 1px solid var(--dash-shell-border);
    padding-top: 1rem;
    margin-top: 1rem;
}
.profile-form-section > h3 {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--dash-shell-muted);
    margin-bottom: 0.85rem;
}

.profile-snapshot-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    object-position: top;
    border-radius: 14px;
    background: #e8edf4;
    display: block;
}
.profile-snapshot-img[src=""] {
    display: none;
}

.profile-stat-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.6rem;
    text-align: center;
}
.profile-stat { padding: 0.75rem 0.5rem; background: var(--dash-shell-panel-soft); border: 1px solid var(--dash-shell-border); border-radius: 12px; }
.profile-stat strong { display: block; font-family: 'Sora', sans-serif; font-size: 1.2rem; color: #0b3668; }
.profile-stat span { font-size: 0.72rem; color: var(--dash-shell-muted); font-weight: 600; display: block; margin-top: 0.15rem; }

.profile-visibility-banner {
    border-radius: 12px;
    padding: 0.85rem;
    font-size: 0.85rem;
    line-height: 1.5;
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
}
.profile-visibility-banner svg { flex-shrink: 0; width: 1rem; height: 1rem; margin-top: 0.15rem; }
.profile-visibility-banner--live    { background: #f0fdf4; border: 1px solid #86efac; color: #15803d; }
.profile-visibility-banner--draft   { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
.profile-visibility-banner--suspend { background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; }

.profile-checklist { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.5rem; }
.profile-checklist li {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.83rem;
    padding: 0.5rem 0.65rem;
    border-radius: 10px;
    border: 1px solid var(--dash-shell-border);
    background: var(--dash-shell-panel-soft);
}
.profile-checklist li.done { border-color: #86efac; background: #f0fdf4; color: #15803d; }
.profile-checklist li svg { width: 0.95rem; height: 0.95rem; flex-shrink: 0; }

.profile-plan {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    border: 1px solid transparent;
}
.profile-plan--starter { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.profile-plan--growth  { background: #fff7ed; border-color: #fed7aa; color: #c2410c; }
.profile-plan--elite   { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
.profile-plan--none    { background: #f8fafc; border-color: #e2e8f0; color: #94a3b8; }
</style>
@endpush

@section('content')
@php
    $agentImage = $agentProfile->headshotPublicUrl($agentUser);
    $profileFields = [
        'Brokerage name'  => filled($agentProfile->brokerage_name),
        'License number'  => filled($agentProfile->license_number),
        'Profile bio'     => filled($agentProfile->bio),
        'Specialties'     => filled($agentProfile->specialties),
        'Service city'    => filled($agentProfile->service_city),
        'Headshot / photo' => filled($agentProfile->headshot),
    ];
    $profileComplete = (int) round(collect($profileFields)->filter()->count() / count($profileFields) * 100);

    $statusVis = match($agentProfile->profile_status ?? 'draft') {
        'published', 'featured' => 'live',
        'suspended' => 'suspend',
        default     => 'draft',
    };

    $visMessages = [
        'live'    => 'Your profile is live and visible in the OmniReferral agent directory. Buyers and sellers can find and contact you.',
        'draft'   => 'Your profile is not published yet. Complete the fields below and the admin team will review it for the directory.',
        'suspend' => 'Your profile has been suspended. Contact support for assistance.',
    ];

    $subscription = $agentUser->activeAgentSubscription;
    $subPackage = $subscription?->package;
    $currentPlan = $agentUser->currentPlan;
    $resolvedPlanName = $subPackage?->displayName() ?? $currentPlan?->displayName() ?? null;
    $resolvedPlanSlug = $subPackage?->slug ?? $currentPlan?->slug ?? null;
    $planBadgeClass = match ($resolvedPlanSlug) {
        'starter-leads', 'quick-leads' => 'profile-plan--starter',
        'growth-leads', 'power-leads' => 'profile-plan--growth',
        'elite-leads', 'prime-leads' => 'profile-plan--elite',
        default => 'profile-plan--none',
    };
@endphp

<div class="workspace-grid workspace-grid--2" style="align-items:start;">

    {{-- Profile Form --}}
    <section class="workspace-card">
        <span class="eyebrow">Edit Profile</span>
        <h2>Update Agent Details</h2>

        {{-- Completeness --}}
        <div style="margin: 0.9rem 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                <span style="font-size:0.8rem; font-weight:700; color:var(--dash-shell-muted);">Profile completeness</span>
                <strong style="font-size:0.82rem; color: {{ $profileComplete >= 80 ? '#15803d' : ($profileComplete >= 50 ? '#d97706' : '#dc2626') }};">{{ $profileComplete }}%</strong>
            </div>
            <div class="profile-completeness-bar">
                <div class="profile-completeness-bar__fill" style="width:{{ $profileComplete }}%;"></div>
            </div>
        </div>

        <form action="{{ route('agent.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="profile-form-section">
                <h3>Personal Information</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field">
                        <span>Full Name <abbr title="required">*</abbr></span>
                        <input type="text" name="name" value="{{ old('name', $agentUser->name) }}" required>
                    </label>
                    <label class="workspace-field">
                        <span>Email Address <abbr title="required">*</abbr></span>
                        <input type="email" name="email" value="{{ old('email', $agentUser->email) }}" required>
                    </label>
                    <label class="workspace-field">
                        <span>Phone Number <abbr title="required">*</abbr></span>
                        <input type="text" name="phone" value="{{ old('phone', $agentUser->phone) }}" placeholder="e.g. (555) 000-0000" required>
                    </label>
                </div>
            </div>

            <div class="profile-form-section">
                <h3>Your Plan</h3>
                <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap; margin-bottom:0.5rem;">
                    @if($resolvedPlanName)
                        <span class="profile-plan {{ $planBadgeClass }}">{{ $resolvedPlanName }}</span>
                        @if($subscription)
                            <span style="font-size:0.8rem; color:var(--dash-shell-muted);">
                                @if($subscription->is_active && $subscription->payment_status === 'paid')
                                    Active
                                @elseif($subscription->ends_at?->isPast())
                                    Expired
                                @else
                                    {{ ucfirst($subscription->payment_status ?: 'Pending') }}
                                @endif
                            </span>
                        @endif
                    @else
                        <span class="profile-plan profile-plan--none">No Plan</span>
                        <span style="font-size:0.8rem; color:var(--dash-shell-muted);">Choose a plan to start receiving leads.</span>
                    @endif
                </div>
                <form action="{{ route('agent.profile.change-plan') }}" method="POST" style="display:flex; align-items:flex-end; gap:0.75rem; flex-wrap:wrap;">
                    @csrf
                    <label class="workspace-field" style="min-width:200px; flex:1;">
                        <span>Change Plan</span>
                        <select name="package_id" required>
                            @foreach($availablePlans as $planOption)
                                <option value="{{ $planOption->id }}" @selected($subPackage?->id === $planOption->id || $currentPlan?->id === $planOption->id)>
                                    {{ $planOption->displayName() }} — {{ $planOption->monthly_lead_quota ?? 0 }} leads/mo
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="button" onclick="return confirm('Change your plan? This will deactivate your current subscription and activate the new one.')" style="min-height:2.6rem;">Update Plan</button>
                </form>
            </div>

            <div class="profile-form-section">
                <h3>Professional Details</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field">
                        <span>Brokerage Name <abbr title="required">*</abbr></span>
                        <input type="text" name="brokerage_name" value="{{ old('brokerage_name', $agentProfile->brokerage_name) }}" placeholder="e.g. Keller Williams Realty" required>
                    </label>
                    <label class="workspace-field">
                        <span>License Number <abbr title="required">*</abbr></span>
                        <input type="text" name="license_number" value="{{ old('license_number', $agentProfile->license_number) }}" placeholder="e.g. TX-12345678" required>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Specialties</span>
                        <input type="text" name="specialties" value="{{ old('specialties', $agentProfile->specialties) }}" placeholder="e.g. Buyer Representation, First-Time Buyers, Investment Properties">
                        <small style="font-size:0.73rem; color:var(--dash-shell-muted); margin-top:0.2rem; display:block;">Comma-separated list. Helps buyers find you for the right property type.</small>
                    </label>
                </div>
            </div>

            <div class="profile-form-section">
                <h3>Service Area</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Street Address <abbr title="required">*</abbr></span>
                        <input type="text" name="address_line_1" value="{{ old('address_line_1', $agentUser->address_line_1) }}" required>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Address Line 2</span>
                        <input type="text" name="address_line_2" value="{{ old('address_line_2', $agentUser->address_line_2) }}" placeholder="Apt, Suite, Unit (optional)">
                    </label>
                    <label class="workspace-field">
                        <span>Primary City <abbr title="required">*</abbr></span>
                        <input type="text" name="city" value="{{ old('city', $agentProfile->service_city ?: $agentUser->city) }}" required>
                    </label>
                    <label class="workspace-field">
                        <span>State (2-letter) <abbr title="required">*</abbr></span>
                        <input type="text" name="state" value="{{ old('state', $agentProfile->service_state ?: $agentUser->state) }}" maxlength="2" placeholder="TX" required>
                    </label>
                    <label class="workspace-field">
                        <span>ZIP Code <abbr title="required">*</abbr></span>
                        <input type="text" name="zip_code" value="{{ old('zip_code', $agentProfile->service_zip_code ?: $agentUser->zip_code) }}" required>
                    </label>
                </div>
            </div>

            <div class="profile-form-section">
                <h3>Bio &amp; Photo</h3>
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Agent Bio</span>
                        <textarea name="bio" style="min-height:130px;" placeholder="Write 2–3 sentences about your experience, market focus, and what makes you the right agent for buyers and sellers in your area.">{{ old('bio', $agentProfile->bio) }}</textarea>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Profile Photo / Headshot</span>
                        <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
                        <small style="font-size:0.73rem; color:var(--dash-shell-muted); margin-top:0.2rem; display:block;">JPG, PNG, or WebP · Max 4 MB · Recommended: 400×400px or larger, professional headshot.</small>
                    </label>
                </div>
            </div>

            <div class="workspace-actions" style="margin-top:1.1rem;">
                <button type="submit" class="button">Save Profile</button>
                <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Cancel</a>
            </div>
        </form>
    </section>

    {{-- Right Column: Snapshot + Status --}}
    <div style="display:grid; gap:1rem;">

        <section class="workspace-card">
            <span class="eyebrow">Public Snapshot</span>
            <h2>How You Appear in the Directory</h2>

            <img
                src="{{ $agentImage }}"
                alt="{{ $agentUser->name }} profile photo"
                class="profile-snapshot-img"
                style="margin-top:0.75rem;"
                onerror="this.style.display='none'; document.getElementById('agent-photo-fallback').style.display='grid';"
            >
            <div id="agent-photo-fallback" style="display:none; width:100%; height:160px; border-radius:14px; background:#e8edf4; place-items:center; margin-top:0.75rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            </div>

            <div style="margin-top:0.9rem; display:grid; gap:0.4rem;">
                <strong style="font-size:1rem;">{{ $agentUser->name }}</strong>
                <span style="font-size:0.85rem; color:var(--dash-shell-muted);">{{ $agentProfile->brokerage_name ?: 'Brokerage pending' }}</span>
                <div class="workspace-pill-row" style="margin-top:0.1rem;">
                    @if($agentProfile->service_city)
                        <span class="workspace-pill">{{ $agentProfile->service_city }}, {{ $agentProfile->service_state }}</span>
                    @endif
                    @if($agentProfile->license_number)
                        <span class="workspace-pill">Lic. {{ $agentProfile->license_number }}</span>
                    @endif
                </div>
                @if($agentProfile->specialties)
                    <p style="font-size:0.8rem; color:var(--dash-shell-muted); margin:0.35rem 0 0; line-height:1.5;">{{ $agentProfile->specialties }}</p>
                @endif
                @if($agentProfile->rating)
                    <div style="display:flex; align-items:center; gap:0.3rem; margin-top:0.2rem;">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="#f59e0b"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <strong style="font-size:0.88rem;">{{ number_format((float) $agentProfile->rating, 1) }}</strong>
                        @if($agentProfile->review_count)
                            <span style="font-size:0.78rem; color:var(--dash-shell-muted);">({{ $agentProfile->review_count }} reviews)</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="profile-stat-row" style="margin-top:1rem;">
                <div class="profile-stat">
                    <strong>{{ number_format($agentStats['leads_received']) }}</strong>
                    <span>Leads</span>
                </div>
                <div class="profile-stat">
                    <strong>{{ number_format($activeListingCount) }}</strong>
                    <span>Listings</span>
                </div>
                <div class="profile-stat">
                    <strong>{{ $agentStats['response_rate'] }}</strong>
                    <span>Response</span>
                </div>
            </div>
        </section>

        <section class="workspace-card">
            <span class="eyebrow">Profile Status</span>
            <h2>Visibility &amp; Completeness</h2>

            <div class="profile-visibility-banner profile-visibility-banner--{{ $statusVis }}" style="margin-top:0.75rem;">
                @if($statusVis === 'live')
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                @else
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                @endif
                {{ $visMessages[$statusVis] }}
            </div>

            <ul class="profile-checklist" style="margin-top:0.85rem;">
                @foreach($profileFields as $label => $done)
                    <li class="{{ $done ? 'done' : '' }}">
                        @if($done)
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @else
                            <svg viewBox="0 0 20 20" fill="currentColor" style="color:#d1d5db;"><circle cx="10" cy="10" r="8"/></svg>
                        @endif
                        {{ $label }}
                        @if(! $done)
                            <span style="margin-left:auto; font-size:0.72rem; font-weight:700; color:#d97706;">Missing</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>

    </div>

</div>
@endsection
