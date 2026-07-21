@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Staff Workspace')
@section('dashboard_title', $user?->publicDisplayName() ?: 'Edit Agent Profile')
@section('dashboard_description', 'Update directory data, manage the subscription package, or change status.')

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
    use App\Support\PlanCapabilities;

    $socialLinks = is_array($profile->social_links) ? $profile->social_links : [];
    $subscription = $user?->activeAgentSubscription;
    $subPackage = $subscription?->package;
    $currentPlan = $user?->currentPlan;

    // Effective plan = current_plan_id (single source of truth); fall back to active subscription package.
    $effectivePackage = $currentPlan ?? $subPackage;
    $resolvedPlanSlug = $effectivePackage?->slug;
    $canonicalSlug = $resolvedPlanSlug ? PlanCapabilities::canonicalize($resolvedPlanSlug) : null;
    $resolvedPlanLabel = $resolvedPlanSlug ? PlanCapabilities::label($resolvedPlanSlug) : null;

    $planBadgeClass = match ($canonicalSlug) {
        'starter-leads' => 'agent-admin__plan--starter',
        'growth-leads' => 'agent-admin__plan--growth',
        'elite-leads' => 'agent-admin__plan--elite',
        'cold-calling-isa', 'social-media-mgmt', 'individual-va' => 'agent-admin__plan--va',
        default => 'agent-admin__plan--none',
    };

    $isActiveSub = $subscription && $subscription->is_active && $subscription->payment_status === 'paid';
    $packageStatus = match (true) {
        ! $effectivePackage => 'No Package',
        $subscription && $subscription->payment_status === 'cancelled' => 'Cancelled',
        $subscription?->ends_at?->isPast() => 'Expired',
        $isActiveSub => 'Active',
        (bool) $subscription => ucfirst($subscription->payment_status ?: 'Pending'),
        default => 'Active',
    };
    $packageStatusClass = match ($packageStatus) {
        'Active' => 'subpkg-pill--ok',
        'Cancelled', 'Expired' => 'subpkg-pill--danger',
        'No Package' => 'subpkg-pill--muted',
        default => 'subpkg-pill--warn',
    };

    // Quick upgrade / downgrade targets across lead tiers.
    $leadRankMap = ['starter-leads' => 1, 'growth-leads' => 2, 'elite-leads' => 3];
    $leadPackages = $availablePlans
        ->filter(fn ($p) => isset($leadRankMap[PlanCapabilities::canonicalize($p->slug)]))
        ->sortBy(fn ($p) => $leadRankMap[PlanCapabilities::canonicalize($p->slug)])
        ->values();
    $currentRank = $canonicalSlug ? ($leadRankMap[$canonicalSlug] ?? null) : null;
    $upgradeTarget = $currentRank
        ? $leadPackages->first(fn ($p) => ($leadRankMap[PlanCapabilities::canonicalize($p->slug)] ?? 0) === $currentRank + 1)
        : null;
    $downgradeTarget = $currentRank
        ? $leadPackages->first(fn ($p) => ($leadRankMap[PlanCapabilities::canonicalize($p->slug)] ?? 0) === $currentRank - 1)
        : null;

    $billingLabel = $effectivePackage?->billing_type ? ucfirst(str_replace('_', ' ', $effectivePackage->billing_type)) : '—';
    $ghlStatus = ($subscription?->ghl_contact_id || $user?->ghl_contact_id) ? 'Connected' : 'Not linked';
    $stripeStatus = $user?->stripe_customer_id ? 'Customer on file' : 'Not connected';
@endphp

<style>
    .subpkg-card { padding: 1.5rem; }
    .subpkg-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr)); gap:1.1rem 1.25rem; }
    .subpkg-field span { display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.04em; margin-bottom:0.2rem; }
    .subpkg-field strong { font-size:0.9rem; color:#0f172a; }
    .subpkg-pill { display:inline-block; padding:0.15rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:700; }
    .subpkg-pill--ok { background:#dcfce7; color:#166534; }
    .subpkg-pill--warn { background:#fef9c3; color:#854d0e; }
    .subpkg-pill--danger { background:#fee2e2; color:#991b1b; }
    .subpkg-pill--muted { background:#e2e8f0; color:#475569; }
    .subpkg-section-title { margin:0 0 1rem; font-size:1rem; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:0.5rem; }
    .subpkg-layout { display:grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); gap:1.5rem; align-items:start; }
    @media (max-width: 820px) { .subpkg-layout { grid-template-columns:1fr; } }
    .subpkg-actions { display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:1rem; }
    .subpkg-actions form { display:inline; }
    /* searchable combobox */
    .subpkg-combo { position:relative; }
    .subpkg-combo__input { width:100%; padding:0.6rem 0.75rem; border:1px solid #cbd5e1; border-radius:0.5rem; font-size:0.9rem; }
    .subpkg-combo__list { position:absolute; z-index:30; top:calc(100% + 4px); left:0; right:0; margin:0; padding:0.35rem; list-style:none; background:#fff; border:1px solid #cbd5e1; border-radius:0.6rem; box-shadow:0 10px 30px rgba(15,23,42,0.12); max-height:280px; overflow:auto; }
    .subpkg-combo__list[hidden] { display:none; }
    .subpkg-combo__option { display:flex; justify-content:space-between; align-items:center; gap:0.75rem; width:100%; text-align:left; padding:0.55rem 0.65rem; border:0; background:transparent; border-radius:0.45rem; cursor:pointer; font-size:0.88rem; color:#0f172a; }
    .subpkg-combo__option small { color:#94a3b8; font-weight:600; text-transform:capitalize; font-size:0.72rem; }
    .subpkg-combo__option:hover, .subpkg-combo__option.is-active { background:#eff6ff; }
    .subpkg-combo__option[hidden] { display:none; }
    .subpkg-combo__option.is-current { background:#f1f5f9; }
    /* checklist */
    .subpkg-checklist { margin:0; padding:0; list-style:none; display:grid; gap:0.4rem; }
    .subpkg-checklist li { display:flex; align-items:center; gap:0.55rem; font-size:0.85rem; padding:0.3rem 0.5rem; border-radius:0.4rem; }
    .subpkg-checklist li.is-on { color:#0f172a; }
    .subpkg-checklist li.is-off { color:#94a3b8; }
    .subpkg-checklist .mark { flex:0 0 auto; width:1.15rem; height:1.15rem; border-radius:999px; display:grid; place-items:center; font-size:0.75rem; font-weight:800; }
    .subpkg-checklist li.is-on .mark { background:#dcfce7; color:#166534; }
    .subpkg-checklist li.is-off .mark { background:#f1f5f9; color:#cbd5e1; }
    .subpkg-checklist-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem; }
    .subpkg-checklist-head h4 { margin:0; font-size:0.9rem; font-weight:800; }
    .subpkg-hist { width:100%; border-collapse:collapse; font-size:0.82rem; }
    .subpkg-hist th, .subpkg-hist td { text-align:left; padding:0.5rem 0.6rem; border-bottom:1px solid #f1f5f9; }
    .subpkg-hist th { font-size:0.68rem; text-transform:uppercase; letter-spacing:0.04em; color:#64748b; }
    .subpkg-badge { display:inline-block; padding:0.1rem 0.5rem; border-radius:999px; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.03em; background:#eef2ff; color:#4338ca; }
</style>

<div class="workspace-stack">
    @if(session('success'))<div class="workspace-card" style="border-left:4px solid #16a34a;">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="workspace-card" style="border-left:4px solid #dc2626;">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="workspace-card" style="border-left:4px solid #dc2626;">{{ $errors->first() }}</div>@endif

    {{-- ============ SUBSCRIPTION & PACKAGE ============ --}}
    <section class="workspace-card subpkg-card">
        <h3 class="subpkg-section-title">💳 Subscription &amp; Package</h3>

        <div class="subpkg-grid" style="margin-bottom:1.5rem;">
            <div class="subpkg-field">
                <span>Current Package</span>
                @if($resolvedPlanLabel)
                    <span class="agent-admin__plan {{ $planBadgeClass }}">{{ $resolvedPlanLabel }}</span>
                @else
                    <span class="agent-admin__plan agent-admin__plan--none">No Plan</span>
                @endif
            </div>
            <div class="subpkg-field"><span>Package Status</span><strong><span class="subpkg-pill {{ $packageStatusClass }}">{{ $packageStatus }}</span></strong></div>
            <div class="subpkg-field"><span>Purchase Date</span><strong>{{ $subscription?->starts_at?->format('M j, Y') ?? '—' }}</strong></div>
            <div class="subpkg-field"><span>Expiry Date</span><strong>{{ $subscription?->ends_at?->format('M j, Y') ?? 'No expiry' }}</strong></div>
            <div class="subpkg-field"><span>Billing Type</span><strong>{{ $billingLabel }}</strong></div>
            <div class="subpkg-field"><span>Payment Method</span><strong>{{ $subscription?->payment_provider ? ucfirst($subscription->payment_provider) : '—' }}</strong></div>
            <div class="subpkg-field"><span>GoHighLevel Status</span><strong>{{ $ghlStatus }}</strong></div>
            <div class="subpkg-field"><span>Stripe Status</span><strong>{{ $stripeStatus }}</strong></div>
            <div class="subpkg-field"><span>Account Status</span><strong>{{ ucfirst($user?->status ?? 'unknown') }}</strong></div>
            @if($effectivePackage?->monthly_lead_quota)
                <div class="subpkg-field"><span>Monthly Lead Quota</span><strong>{{ $effectivePackage->monthly_lead_quota }} / mo</strong></div>
            @endif
        </div>

        <div class="subpkg-layout">
            {{-- LEFT: assign / change --}}
            <div>
                <form method="POST" action="{{ route('admin.agent-profiles.change-plan', $profile) }}" id="subpkg-change-form">
                    @csrf
                    <label class="subpkg-field" style="display:block;">
                        <span>Select Purchased Package</span>
                        <div class="subpkg-combo" data-combo>
                            <input type="hidden" name="package_id" value="{{ $effectivePackage?->id }}" data-combo-value required>
                            <input type="text" class="subpkg-combo__input" placeholder="Search packages…" autocomplete="off"
                                   data-combo-search value="{{ $resolvedPlanLabel ?? '' }}" aria-label="Search purchased package">
                            <ul class="subpkg-combo__list" data-combo-list hidden>
                                @foreach($availablePlans as $planOption)
                                    <li>
                                        <button type="button" class="subpkg-combo__option {{ $effectivePackage?->id === $planOption->id ? 'is-current' : '' }}"
                                                data-id="{{ $planOption->id }}"
                                                data-slug="{{ PlanCapabilities::canonicalize($planOption->slug) }}"
                                                data-label="{{ PlanCapabilities::label($planOption->slug) }}">
                                            {{ PlanCapabilities::label($planOption->slug) }}
                                            <small>{{ $planOption->category === 'virtual_assistant' ? 'VA Service' : 'Real Estate' }}</small>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </label>

                    <div class="subpkg-actions">
                        <button type="submit" class="button button--orange">Save / Apply Package</button>

                        @if($upgradeTarget)
                            <button type="submit" class="button button--blue" formaction="{{ route('admin.agent-profiles.change-plan', $profile) }}"
                                    onclick="document.querySelector('#subpkg-change-form [data-combo-value]').value='{{ $upgradeTarget->id }}'">
                                ⬆ Upgrade → {{ PlanCapabilities::label($upgradeTarget->slug) }}
                            </button>
                        @endif
                        @if($downgradeTarget)
                            <button type="submit" class="button button--ghost-blue"
                                    onclick="document.querySelector('#subpkg-change-form [data-combo-value]').value='{{ $downgradeTarget->id }}'">
                                ⬇ Downgrade → {{ PlanCapabilities::label($downgradeTarget->slug) }}
                            </button>
                        @endif
                    </div>
                </form>

                <div class="subpkg-actions">
                    @if($user?->current_plan_id)
                        <form method="POST" action="{{ route('admin.agent-profiles.cancel-plan', $profile) }}"
                              onsubmit="return confirm('Cancel this package? The agent will lose all plan features immediately.');">
                            @csrf
                            <button type="submit" class="button button--ghost-blue" style="color:#b91c1c;">Cancel Package</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.agent-profiles.reactivate-plan', $profile) }}">
                            @csrf
                            <button type="submit" class="button button--ghost-blue">Reactivate Package</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- RIGHT: live feature checklist --}}
            <div class="workspace-card" style="background:#f8fafc;">
                <div class="subpkg-checklist-head">
                    <h4>Included Features</h4>
                    <span class="subpkg-badge" data-checklist-label>{{ $resolvedPlanLabel ?? 'No Plan' }}</span>
                </div>
                <ul class="subpkg-checklist" data-checklist>
                    @forelse($currentChecklist as $item)
                        <li class="{{ $item['enabled'] ? 'is-on' : 'is-off' }}">
                            <span class="mark">{{ $item['enabled'] ? '✔' : '✕' }}</span>
                            <span>{{ $item['label'] }}</span>
                        </li>
                    @empty
                        <li class="is-off"><span class="mark">–</span><span>Select a package to preview its features.</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
    </section>

    {{-- ============ SUBSCRIPTION HISTORY ============ --}}
    @if($subscriptionHistory->isNotEmpty())
        <section class="workspace-card subpkg-card">
            <h3 class="subpkg-section-title">🕑 Subscription History</h3>
            <div style="overflow-x:auto;">
                <table class="subpkg-hist">
                    <thead>
                        <tr><th>When</th><th>Action</th><th>Change</th><th>By</th></tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptionHistory as $entry)
                            <tr>
                                <td>{{ $entry->created_at?->format('M j, Y g:i A') }}</td>
                                <td><span class="subpkg-badge">{{ $entry->actionLabel() }}</span></td>
                                <td>{{ $entry->note ?? '—' }}</td>
                                <td>{{ $entry->performedByUser?->name ?? ucfirst($entry->performed_by) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- ============ PROFILE DETAILS ============ --}}
    <section class="workspace-card">
        <h3 class="subpkg-section-title">📝 Profile Details</h3>
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

<script type="application/json" data-plan-catalog>@json($planCatalog)</script>
<script>
(function () {
    const catalog = JSON.parse(document.querySelector('[data-plan-catalog]').textContent || '{}');
    const combo = document.querySelector('[data-combo]');
    if (!combo) return;

    const search = combo.querySelector('[data-combo-search]');
    const list = combo.querySelector('[data-combo-list]');
    const hidden = combo.querySelector('[data-combo-value]');
    const options = Array.from(combo.querySelectorAll('[data-combo-option], .subpkg-combo__option'));
    const checklistEl = document.querySelector('[data-checklist]');
    const checklistLabel = document.querySelector('[data-checklist-label]');

    function renderChecklist(slug, label) {
        const entry = catalog[slug];
        if (checklistLabel) checklistLabel.textContent = (entry && entry.label) || label || 'No Plan';
        if (!checklistEl) return;
        const items = (entry && entry.checklist) || [];
        if (!items.length) {
            checklistEl.innerHTML = '<li class="is-off"><span class="mark">–</span><span>No feature data for this package.</span></li>';
            return;
        }
        checklistEl.innerHTML = items.map(function (i) {
            const cls = i.enabled ? 'is-on' : 'is-off';
            const mark = i.enabled ? '✔' : '✕';
            const label = String(i.label).replace(/</g, '&lt;');
            return '<li class="' + cls + '"><span class="mark">' + mark + '</span><span>' + label + '</span></li>';
        }).join('');
    }

    function openList() { list.hidden = false; }
    function closeList() { list.hidden = true; }

    search.addEventListener('focus', openList);
    search.addEventListener('click', openList);
    search.addEventListener('input', function () {
        const q = search.value.trim().toLowerCase();
        openList();
        options.forEach(function (opt) {
            opt.hidden = q !== '' && !opt.textContent.toLowerCase().includes(q);
        });
    });

    options.forEach(function (opt) {
        opt.addEventListener('click', function () {
            hidden.value = opt.dataset.id;
            search.value = opt.dataset.label;
            options.forEach(function (o) { o.classList.remove('is-active'); });
            opt.classList.add('is-active');
            renderChecklist(opt.dataset.slug, opt.dataset.label);
            closeList();
        });
    });

    document.addEventListener('click', function (e) {
        if (!combo.contains(e.target)) closeList();
    });
})();
</script>
@endsection
