@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Operations')
@section('dashboard_title', 'Agent Profiles')
@section('dashboard_description', 'Review, edit, approve, feature, suspend, and reactivate agent directory profiles from one responsive workspace.')

@push('styles')
    @vite('resources/css/modules/admin-agent-profiles.css')
@endpush

@section('dashboard_actions')
    <a href="{{ route('admin.agents.import') }}" class="button button--orange">Add Agent Profile</a>
@endsection

@section('content')
@php
    $statusTabs = ['all' => 'All', 'draft' => 'Pending', 'approved' => 'Approved', 'published' => 'Approved (Legacy)', 'featured' => 'Featured', 'suspended' => 'Suspended'];
    $perPageOptions = [10, 25, 50, 100];
    
    $completionFor = function ($profile): int {
        $user = $profile->user;
        $fields = [
            $user?->name,
            $profile->slug,
            $user?->email,
            $user?->phone,
            $profile->brokerage_name,
            $profile->is_active_agent === null ? true : $profile->is_active_agent,
            $profile->service_city,
            $profile->service_state,
            $profile->service_zip_code,
            $profile->market_areas,
            $profile->bio,
            $profile->headshot,
            $profile->specialties,
            $profile->languages,
            $profile->social_links,
        ];

        $filled = collect($fields)->filter(function ($value) {
            if (is_array($value)) {
                return count(array_filter($value)) > 0;
            }
            return trim((string) $value) !== '';
        })->count();

        return (int) round(($filled / count($fields)) * 100);
    };
    
    $formatDate = fn ($date) => $date ? $date->format('M j, Y') : '-';

    $planLabelFor = function ($profile): string {
        $sub = $profile->user?->activeAgentSubscription;
        if ($sub && $sub->package) {
            return $sub->package->displayName();
        }
        $plan = $profile->user?->currentPlan;
        return $plan ? $plan->displayName() : 'No Plan';
    };

    $planBadgeClassFor = function ($profile): string {
        $sub = $profile->user?->activeAgentSubscription;
        $slug = $sub?->package?->slug ?? $profile->user?->currentPlan?->slug ?? '';
        return match ($slug) {
            'starter-leads', 'quick-leads' => 'agent-admin__plan--starter',
            'growth-leads', 'power-leads' => 'agent-admin__plan--growth',
            'elite-leads', 'prime-leads' => 'agent-admin__plan--elite',
            default => 'agent-admin__plan--none',
        };
    };

    $planStatusFor = function ($profile): string {
        $sub = $profile->user?->activeAgentSubscription;
        if (! $sub) {
            return 'No Subscription';
        }
        if ($sub->is_active && $sub->payment_status === 'paid') {
            return 'Active';
        }
        if ($sub->ends_at && $sub->ends_at->isPast()) {
            return 'Expired';
        }
        return ucfirst($sub->payment_status ?: 'Pending');
    };
@endphp

<div class="agent-admin" data-agent-admin>
    {{-- KPI Status Counters --}}
    <section class="agent-admin__stats" aria-label="Agent profile status counts">
        @foreach($statusTabs as $key => $label)
            <a href="{{ route('admin.agents.manage', array_filter(['status' => $key, 'q' => $filters['q'] ?? null, 'per_page' => $filters['per_page'] ?? 25])) }}"
                class="agent-admin__stat {{ $status === $key ? 'is-active' : '' }}" data-status-tab="{{ $key }}">
                <span>{{ $label }}</span>
                <strong data-status-count="{{ $key }}">{{ number_format($counts[$key] ?? 0) }}</strong>
            </a>
        @endforeach
    </section>

    {{-- Filter Panel --}}
    <section class="agent-admin__filters">
        <div class="agent-admin__section-head">
            <div>
                <span class="eyebrow" style="text-transform:uppercase; font-size:0.75rem; font-weight:700; color:var(--agent-admin-muted);">Filters</span>
                <h2>Search &amp; Filter Agents</h2>
            </div>
            <p>Refine your search by name, email, brokerage, license, city, state, ZIP, or featured status.</p>
        </div>

        <form method="GET" action="{{ route('admin.agents.manage') }}" class="agent-admin__filter-grid">
            <label class="agent-admin__field agent-admin__field--wide">
                <span>Search Query</span>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email, phone, brokerage, license, ZIP...">
            </label>

            <label class="agent-admin__field">
                <span>Status</span>
                <select name="status">
                    @foreach($statusTabs as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? 'all') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="agent-admin__field">
                <span>Market / City</span>
                <select name="market">
                    <option value="">All markets</option>
                    @foreach($filterMarkets as $market)
                        <option value="{{ $market }}" @selected(($filters['market'] ?? '') === $market)>{{ $market }}</option>
                    @endforeach
                </select>
            </label>

            <label class="agent-admin__field">
                <span>State</span>
                <select name="state">
                    <option value="">All states</option>
                    @foreach($filterStates as $stateOption)
                        <option value="{{ $stateOption }}" @selected(($filters['state'] ?? '') === strtoupper($stateOption))>{{ $stateOption }}</option>
                    @endforeach
                </select>
            </label>

            <label class="agent-admin__field">
                <span>Brokerage</span>
                <select name="brokerage">
                    <option value="">All brokerages</option>
                    @foreach($filterBrokerages as $brokerage)
                        <option value="{{ $brokerage }}" @selected(($filters['brokerage'] ?? '') === $brokerage)>{{ $brokerage }}</option>
                    @endforeach
                </select>
            </label>

            <label class="agent-admin__field">
                <span>Featured Status</span>
                <select name="featured">
                    <option value="" @selected(($filters['featured'] ?? '') === '')>Any</option>
                    <option value="yes" @selected(($filters['featured'] ?? '') === 'yes')>Featured only</option>
                    <option value="no" @selected(($filters['featured'] ?? '') === 'no')>Not featured</option>
                </select>
            </label>

            <label class="agent-admin__field">
                <span>Pricing Plan</span>
                <select name="plan">
                    <option value="all" @selected(($filters['plan'] ?? 'all') === 'all')>All plans</option>
                    @foreach($availablePlans as $planOption)
                        <option value="{{ $planOption->slug }}" @selected(($filters['plan'] ?? 'all') === $planOption->slug)>{{ $planOption->displayName() }}</option>
                    @endforeach
                    <option value="none" @selected(($filters['plan'] ?? '') === 'none')">No Plan</option>
                </select>
            </label>

            <label class="agent-admin__field">
                <span>Rows Per Page</span>
                <select name="per_page">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected((int) ($filters['per_page'] ?? 25) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>

            <div class="agent-admin__filter-actions">
                <a href="{{ route('admin.agents.manage') }}" class="button button--ghost-blue">Reset Filters</a>
                <button type="submit" class="button button--orange">Apply Filters</button>
            </div>
        </form>
    </section>

    {{-- Main Content Panel --}}
    <section class="agent-admin__panel">
        <div class="agent-admin__table-head">
            <div>
                <span class="eyebrow" style="text-transform:uppercase; font-size:0.75rem; font-weight:700; color:var(--agent-admin-muted);">Matching Profiles</span>
                <h2>{{ number_format($profiles->total()) }} Agent Profiles</h2>
            </div>
            <p>
                Showing {{ number_format($profiles->firstItem() ?? 0) }}–{{ number_format($profiles->lastItem() ?? 0) }}
                of {{ number_format($profiles->total()) }} agents
            </p>
        </div>

        {{-- Desktop Table View --}}
        <div class="agent-admin__table-wrap">
            <table class="agent-admin__table">
                <thead>
                    <tr>
                        <th>Agent Info</th>
                        <th>Contact info</th>
                        <th>Brokerage &amp; Active Status</th>
                        <th>Market Location</th>
                        <th>Status</th>
                        <th>Plan</th>
                        <th>Completion</th>
                        <th>Created / Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profiles as $profile)
                        @php
                            $user = $profile->user;
                            $socialLinks = is_array($profile->social_links) ? $profile->social_links : [];
                            $completion = $completionFor($profile);
                            $publicUrl = $profile->isPublicVisible() ? route('agents.profile', $profile) : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="agent-admin__identity">
                                    <img src="{{ $profile->headshotPublicUrl($user) }}" alt="" width="40" height="40" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/realtors/logo-bydefault_agent.png') }}'">
                                    <div>
                                        <strong>{{ $user?->publicDisplayName() ?? 'Unnamed agent' }}</strong>
                                        <span class="agent-admin__plan {{ $planBadgeClassFor($profile) }}">{{ $planLabelFor($profile) }}</span>
                                        <span>{{ $profile->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="agent-admin__stack">
                                    <span>{{ $user?->email ?? '-' }}</span>
                                    <small>{{ $user?->phone ?? '-' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="agent-admin__stack">
                                    <span>{{ $profile->brokerage_name ?: '-' }}</span>
                                    <small>{{ ($profile->is_active_agent ?? true) ? 'Active Agent' : 'Not Active' }}</small>
                                    <small>Source: {{ $profile->submission_source === 'public_agents_page' ? 'Public Agents Page' : ($profile->submission_source ?: 'Admin') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="agent-admin__stack">
                                    <span>{{ $profile->service_city ?: '-' }}, {{ $profile->service_state ?: '-' }}</span>
                                    <small>ZIP {{ $profile->service_zip_code ?: '-' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="agent-admin__status agent-admin__status--{{ $profile->profile_status }}">{{ $profile->statusLabel() }}</span>
                            </td>
                            <td>
                                <div class="agent-admin__stack">
                                    <span class="agent-admin__plan agent-admin__plan--row {{ $planBadgeClassFor($profile) }}">{{ $planLabelFor($profile) }}</span>
                                    <small>{{ $planStatusFor($profile) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="agent-admin__completion">
                                    <span><i style="width: {{ $completion }}%"></i></span>
                                    <strong>{{ $completion }}%</strong>
                                </div>
                            </td>
                            <td>
                                <div class="agent-admin__stack">
                                    <span>{{ $profile->createdByUser?->name ?? 'System' }}</span>
                                    <small>Created {{ $formatDate($profile->created_at) }}</small>
                                    <small>Updated {{ $formatDate($profile->updated_at) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="agent-admin__actions" data-agent-id="{{ $profile->id }}" data-agent-name="{{ $user?->publicDisplayName() ?? 'Unnamed agent' }}" data-agent-email="{{ $user?->email ?? '-' }}" data-agent-slug="{{ $profile->slug }}">
                                    {{-- Status Transition Buttons Group --}}
                                    <div class="agent-admin__status-actions-group">
                                        @if($profile->profile_status === \App\Models\RealtorProfile::STATUS_DRAFT)
                                            <button type="button" class="button agent-admin__button-success" data-action="approve" data-url="{{ route('admin.agent-profiles.publish', $profile) }}">Approve</button>
                                        @elseif(in_array($profile->profile_status, [\App\Models\RealtorProfile::STATUS_APPROVED, \App\Models\RealtorProfile::STATUS_PUBLISHED], true))
                                            <button type="button" class="button agent-admin__button-feature" data-action="feature" data-url="{{ route('admin.agent-profiles.feature', $profile) }}">Feature</button>
                                            <button type="button" class="button agent-admin__button-danger" data-action="suspend" data-url="{{ route('admin.agent-profiles.suspend', $profile) }}">Suspend</button>
                                        @elseif($profile->profile_status === \App\Models\RealtorProfile::STATUS_FEATURED)
                                            <button type="button" class="button button--ghost-blue" data-action="unfeature" data-url="{{ route('admin.agent-profiles.publish', $profile) }}">Unfeature</button>
                                            <button type="button" class="button agent-admin__button-danger" data-action="suspend" data-url="{{ route('admin.agent-profiles.suspend', $profile) }}">Suspend</button>
                                        @elseif($profile->profile_status === \App\Models\RealtorProfile::STATUS_SUSPENDED)
                                            <button type="button" class="button agent-admin__button-success" data-action="reactivate" data-url="{{ route('admin.agent-profiles.publish', $profile) }}">Reactivate</button>
                                        @endif
                                    </div>
                                    
                                    <button type="button" class="button button--ghost-blue" data-open-dialog="details-{{ $profile->id }}">Details</button>
                                    <button type="button" class="button button--ghost-blue" data-open-dialog="edit-{{ $profile->id }}">Edit</button>
                                    
                                    <a href="{{ route('agents.profile', $profile) }}" target="_blank" rel="noopener" class="button button--ghost-blue data-view-link" style="{{ $profile->isPublicVisible() ? '' : 'display: none;' }}">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="agent-admin__empty">
                                    <h3>No Agent Profiles Found</h3>
                                    <p>Adjust your search filters or add a new agent profile to start.</p>
                                    <a href="{{ route('admin.agents.import') }}" class="button button--orange">Add Agent Profile</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Layout --}}
        <div class="agent-admin__mobile-list">
            @foreach($profiles as $profile)
                @php
                    $user = $profile->user;
                    $completion = $completionFor($profile);
                    $publicUrl = $profile->isPublicVisible() ? route('agents.profile', $profile) : null;
                @endphp
                <article class="agent-admin__mobile-card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                        <div class="agent-admin__identity">
                            <img src="{{ $profile->headshotPublicUrl($user) }}" alt="" width="40" height="40" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/realtors/logo-bydefault_agent.png') }}'">
                            <div>
                                <strong>{{ $user?->publicDisplayName() ?? 'Unnamed agent' }}</strong>
                                <span class="agent-admin__plan {{ $planBadgeClassFor($profile) }}">{{ $planLabelFor($profile) }}</span>
                                <span>{{ $user?->email ?? '-' }}</span>
                            </div>
                        </div>
                        <span class="agent-admin__status agent-admin__status--{{ $profile->profile_status }}">{{ $profile->statusLabel() }}</span>
                    </div>
                    
                    <dl>
                        <div>
                            <dt>Brokerage</dt>
                            <dd>{{ $profile->brokerage_name ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt>Pricing Plan</dt>
                            <dd><span class="agent-admin__plan {{ $planBadgeClassFor($profile) }}">{{ $planLabelFor($profile) }}</span></dd>
                        </div>
                        <div>
                            <dt>Market Location</dt>
                            <dd>{{ $profile->service_city ?: '-' }}, {{ $profile->service_state ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt>Active Agent</dt>
                            <dd>{{ ($profile->is_active_agent ?? true) ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div>
                            <dt>Source</dt>
                            <dd>{{ $profile->submission_source === 'public_agents_page' ? 'Public Agents Page' : ($profile->submission_source ?: 'Admin') }}</dd>
                        </div>
                        <div>
                            <dt>Completion</dt>
                            <dd>{{ $completion }}%</dd>
                        </div>
                    </dl>
                    
                    <div class="agent-admin__actions" data-agent-id="{{ $profile->id }}" data-agent-name="{{ $user?->publicDisplayName() ?? 'Unnamed agent' }}" data-agent-email="{{ $user?->email ?? '-' }}" data-agent-slug="{{ $profile->slug }}">
                        <div class="agent-admin__status-actions-group" style="display:contents;">
                            @if($profile->profile_status === \App\Models\RealtorProfile::STATUS_DRAFT)
                                <button type="button" class="button agent-admin__button-success" data-action="approve" data-url="{{ route('admin.agent-profiles.publish', $profile) }}">Approve</button>
                            @elseif(in_array($profile->profile_status, [\App\Models\RealtorProfile::STATUS_APPROVED, \App\Models\RealtorProfile::STATUS_PUBLISHED], true))
                                <button type="button" class="button agent-admin__button-feature" data-action="feature" data-url="{{ route('admin.agent-profiles.feature', $profile) }}">Feature</button>
                                <button type="button" class="button agent-admin__button-danger" data-action="suspend" data-url="{{ route('admin.agent-profiles.suspend', $profile) }}">Suspend</button>
                            @elseif($profile->profile_status === \App\Models\RealtorProfile::STATUS_FEATURED)
                                <button type="button" class="button button--ghost-blue" data-action="unfeature" data-url="{{ route('admin.agent-profiles.publish', $profile) }}">Unfeature</button>
                                <button type="button" class="button agent-admin__button-danger" data-action="suspend" data-url="{{ route('admin.agent-profiles.suspend', $profile) }}">Suspend</button>
                            @elseif($profile->profile_status === \App\Models\RealtorProfile::STATUS_SUSPENDED)
                                <button type="button" class="button agent-admin__button-success" data-action="reactivate" data-url="{{ route('admin.agent-profiles.publish', $profile) }}">Reactivate</button>
                            @endif
                        </div>
                        
                        <button type="button" class="button button--ghost-blue" data-open-dialog="details-{{ $profile->id }}">Details</button>
                        <button type="button" class="button button--ghost-blue" data-open-dialog="edit-{{ $profile->id }}">Edit</button>
                        
                        <a href="{{ route('agents.profile', $profile) }}" target="_blank" rel="noopener" class="button button--ghost-blue data-view-link" style="{{ $profile->isPublicVisible() ? '' : 'display: none;' }}">View</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination Row --}}
        <div class="agent-admin__pagination">
            <p>
                Showing {{ number_format($profiles->firstItem() ?? 0) }}–{{ number_format($profiles->lastItem() ?? 0) }}
                of {{ number_format($profiles->total()) }} agents
            </p>
            {{ $profiles->links() }}
        </div>
    </section>

    {{-- Details & Edit Dialogs loop --}}
    @foreach($profiles as $profile)
        @php
            $user = $profile->user;
            $socialLinks = is_array($profile->social_links) ? $profile->social_links : [];
            $completion = $completionFor($profile);
            $publicUrl = $profile->isPublicVisible() ? route('agents.profile', $profile) : null;
        @endphp

        {{-- Details Dialog Modal --}}
        <dialog class="agent-admin__dialog" id="details-{{ $profile->id }}">
            <div class="agent-admin__dialog-panel">
                <div class="agent-admin__dialog-head">
                    <div>
                        <span class="eyebrow" style="text-transform:uppercase; font-size:0.75rem; font-weight:700; color:var(--agent-admin-muted);">Profile details</span>
                        <h2 style="font-size:1.35rem;">{{ $user?->publicDisplayName() ?? 'Unnamed agent' }}</h2>
                        <p>{{ $user?->email ?? '-' }}</p>
                    </div>
                    <button type="button" class="agent-admin__dialog-close" data-close-dialog aria-label="Close">&times;</button>
                </div>
                <div class="agent-admin__detail-grid">
                    <div><span>Username / slug</span><strong>{{ $profile->slug }}</strong></div>
                    <div><span>Phone</span><strong>{{ $user?->phone ?? '-' }}</strong></div>
                    <div><span>Brokerage</span><strong>{{ $profile->brokerage_name ?: '-' }}</strong></div>
                    <div><span>License</span><strong>{{ $profile->license_number ?: '-' }}</strong></div>
                    <div><span>Active Agent</span><strong>{{ ($profile->is_active_agent ?? true) ? 'Yes' : 'No' }}</strong></div>
                    <div><span>Source</span><strong>{{ $profile->submission_source === 'public_agents_page' ? 'Public Agents Page' : ($profile->submission_source ?: 'Admin') }}</strong></div>
                    <div><span>Pricing Plan</span><strong><span class="agent-admin__plan {{ $planBadgeClassFor($profile) }}">{{ $planLabelFor($profile) }}</span></strong></div>
                    <div><span>Plan Status</span><strong>{{ $planStatusFor($profile) }}</strong></div>
                    @if($user?->activeAgentSubscription?->package)
                        <div><span>Monthly Leads</span><strong>{{ $user->activeAgentSubscription->package->monthly_lead_quota ?? '-' }}/mo</strong></div>
                    @endif
                    <div><span>Market Area</span><strong>{{ $profile->market_areas ?: $profile->service_city ?: '-' }}</strong></div>
                    <div><span>City / State</span><strong>{{ $profile->service_city ?: '-' }}, {{ $profile->service_state ?: '-' }}</strong></div>
                    <div><span>ZIP Code</span><strong>{{ $profile->service_zip_code ?: '-' }}</strong></div>
                    <div><span>Status</span><strong>{{ $profile->statusLabel() }}</strong></div>
                    <div><span>Profile completion</span><strong>{{ $completion }}%</strong></div>
                    <div><span>Created by</span><strong>{{ $profile->createdByUser?->name ?? 'System' }}</strong></div>
                    <div><span>Created Date</span><strong>{{ $formatDate($profile->created_at) }}</strong></div>
                    <div><span>Updated Date</span><strong>{{ $formatDate($profile->updated_at) }}</strong></div>
                </div>
                <div class="agent-admin__bio">
                    <span>Agent Bio</span>
                    <p>{{ $profile->bio ?: 'No bio has been added yet.' }}</p>
                </div>
                <div class="agent-admin__dialog-actions">
                    @if($publicUrl)
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="button button--ghost-blue">View Public Profile</a>
                    @endif
                    <button type="button" class="button button--orange" data-close-dialog data-trigger-edit="edit-{{ $profile->id }}">Edit Profile</button>
                </div>
            </div>
        </dialog>

        {{-- Sliding Right Drawer for Edit --}}
        <dialog class="agent-admin__dialog agent-admin__drawer" id="edit-{{ $profile->id }}">
            <div class="agent-admin__drawer-panel">
                <div class="agent-admin__drawer-head">
                    <div>
                        <span class="eyebrow" style="text-transform:uppercase; font-size:0.75rem; font-weight:700; color:var(--agent-admin-muted);">Modify Profile</span>
                        <h2>{{ $user?->publicDisplayName() ?? 'Unnamed agent' }}</h2>
                        <p>All updates take effect in the public directory immediately after saving.</p>
                    </div>
                    <button type="button" class="agent-admin__dialog-close" data-close-dialog aria-label="Close">&times;</button>
                </div>

                <div class="agent-admin__drawer-scroll">
                    {{-- Plan Change Section --}}
                    <div class="agent-admin__plan-section">
                        <div class="agent-admin__plan-section-head">
                            <span class="agent-admin__plan {{ $planBadgeClassFor($profile) }}">{{ $planLabelFor($profile) }}</span>
                            <span class="agent-admin__plan-section-label">{{ $planStatusFor($profile) }}</span>
                        </div>
                        <form method="POST" action="{{ route('admin.agent-profiles.change-plan', $profile) }}" class="agent-admin__plan-change-form">
                            @csrf
                            <div class="agent-admin__plan-change">
                                <label class="agent-admin__plan-change-select">
                                    <select name="package_id" required>
                                        @foreach($availablePlans as $planOption)
                                            <option value="{{ $planOption->id }}" @selected($user?->activeAgentSubscription?->package_id === $planOption->id || $user?->current_plan_id === $planOption->id)>
                                                {{ $planOption->displayName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <button type="submit" class="button button--ghost-blue" onclick="return confirm('Change this agent\'s plan? This will deactivate the current subscription.')">Update Plan</button>
                            </div>
                        </form>
                    </div>

                    {{-- Edit Profile Form --}}
                    <form id="edit-form-{{ $profile->id }}" method="POST" action="{{ route('admin.agent-profiles.update', $profile) }}" enctype="multipart/form-data" class="agent-admin__edit-form">
                        @csrf
                        @method('PUT')

                        <div class="agent-admin__edit-grid">
                            <label><span>Name</span><input type="text" name="name" value="{{ old('name', $user?->name) }}" required></label>
                            <label><span>Display name</span><input type="text" name="display_name" value="{{ old('display_name', $user?->display_name) }}"></label>
                            <label class="agent-admin__field-full"><span>Email Address</span><input type="email" name="email" value="{{ old('email', $user?->email) }}"></label>
                            <label><span>Phone Number</span><input type="text" name="phone" value="{{ old('phone', $user?->phone) }}"></label>
                            <label><span>Brokerage Name</span><input type="text" name="brokerage_name" value="{{ old('brokerage_name', $profile->brokerage_name) }}" required></label>
                            <label><span>License Number</span><input type="text" name="license_number" value="{{ old('license_number', $profile->license_number) }}"></label>
                            <label><span>Active Agent</span>
                                <select name="is_active_agent">
                                    <option value="1" @selected((string) old('is_active_agent', (int) ($profile->is_active_agent ?? true)) === '1')>Yes</option>
                                    <option value="0" @selected((string) old('is_active_agent', (int) ($profile->is_active_agent ?? true)) === '0')>No</option>
                                </select>
                            </label>
                            <label><span>City</span><input type="text" name="service_city" value="{{ old('service_city', $profile->service_city) }}" required></label>
                            <label><span>State</span><input type="text" name="service_state" value="{{ old('service_state', $profile->service_state) }}" maxlength="2" required></label>
                            <label><span>ZIP Code</span><input type="text" name="service_zip_code" value="{{ old('service_zip_code', $profile->service_zip_code) }}"></label>
                            <label class="agent-admin__field-full"><span>Market Areas</span><input type="text" name="market_areas" value="{{ old('market_areas', $profile->market_areas) }}"></label>
                            <label><span>Years of Experience</span><input type="number" name="years_of_experience" value="{{ old('years_of_experience', $profile->years_of_experience) }}" min="0" max="60"></label>
                            <label><span>Languages</span><input type="text" name="languages" value="{{ old('languages', $profile->languages) }}"></label>
                            <label class="agent-admin__field-full"><span>Status</span>
                                <select name="profile_status" required>
                                    @foreach(\App\Models\RealtorProfile::statusOptions() as $value => $label)
                                        <option value="{{ $value }}" @selected(old('profile_status', $profile->profile_status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label><span>Rating</span><input type="number" step="0.1" name="rating" value="{{ old('rating', $profile->rating) }}" min="0" max="5"></label>
                            <label><span>Reviews Count</span><input type="number" name="review_count" value="{{ old('review_count', $profile->review_count) }}" min="0"></label>
                            <label class="agent-admin__field-full"><span>Leads Closed</span><input type="number" name="leads_closed" value="{{ old('leads_closed', $profile->leads_closed) }}" min="0"></label>
                            <label class="agent-admin__field-full"><span>Specialties</span><input type="text" name="specialties_text" value="{{ old('specialties_text', $profile->specialties) }}"></label>
                            <label class="agent-admin__field-full"><span>Website URL</span><input type="url" name="website_url" value="{{ old('website_url', $socialLinks['website'] ?? '') }}"></label>
                            <label class="agent-admin__field-full"><span>LinkedIn URL</span><input type="url" name="social_linkedin_url" value="{{ old('social_linkedin_url', $socialLinks['linkedin'] ?? '') }}"></label>
                            <label class="agent-admin__field-full"><span>Facebook URL</span><input type="url" name="social_facebook_url" value="{{ old('social_facebook_url', $socialLinks['facebook'] ?? '') }}"></label>
                            <label class="agent-admin__field-full"><span>Instagram URL</span><input type="url" name="social_instagram_url" value="{{ old('social_instagram_url', $socialLinks['instagram'] ?? '') }}"></label>
                            <label class="agent-admin__field-full"><span>Profile Image</span><input type="file" name="headshot" accept="image/*"></label>
                            <label class="agent-admin__field-full"><span>Image URL</span><input type="url" name="headshot_url" value="{{ old('headshot_url') }}"></label>
                            <label class="agent-admin__field-full"><span>Source URL</span><input type="url" name="source_url" value="{{ old('source_url', $profile->source_url) }}"></label>
                            <input type="hidden" name="submission_source" value="{{ old('submission_source', $profile->submission_source) }}">
                            <label class="agent-admin__field-full"><span>Bio</span><textarea name="bio" rows="6" required>{{ old('bio', $profile->bio) }}</textarea></label>
                        </div>
                    </form>
                </div>

                <div class="agent-admin__drawer-actions">
                    <button type="button" class="button button--ghost-blue" data-close-dialog>Cancel</button>
                    <button type="submit" form="edit-form-{{ $profile->id }}" class="button button--orange" data-saving-label="Saving...">Save Changes</button>
                </div>
            </div>
        </dialog>
    @endforeach

    {{-- Shared Confirmation Dialog Modal --}}
    <dialog id="confirm-modal" class="agent-admin__dialog agent-admin__dialog--confirm">
        <div class="agent-admin__dialog-panel">
            <div class="agent-admin__dialog-head">
                <h2>Confirm Status Change</h2>
                <button type="button" class="agent-admin__dialog-close" data-close-confirm>&times;</button>
            </div>
            <div class="agent-admin__confirm-content">
                <p id="confirm-text">Are you sure you want to perform this action?</p>
                <div class="agent-admin__confirm-agent-details">
                    <strong id="confirm-agent-name">-</strong>
                    <span id="confirm-agent-email">-</span>
                </div>
            </div>
            <div class="agent-admin__drawer-actions" style="border:0; padding:0.5rem 0 0; background:transparent;">
                <button type="button" class="button button--ghost-blue" data-close-confirm>Cancel</button>
                <button type="button" class="button button--orange" id="btn-confirm-action">Confirm</button>
            </div>
        </div>
    </dialog>

    {{-- Toast Alerts --}}
    <div id="agent-toast" class="agent-admin__toast">
        <span class="agent-toast-icon">✓</span>
        <span class="agent-toast-content" id="agent-toast-message">Status updated successfully.</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = '{{ csrf_token() }}';

    // Show/close Dialogs
    document.querySelectorAll('[data-open-dialog]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const dialog = document.getElementById(trigger.dataset.openDialog);
            if (!dialog) return;
            document.querySelectorAll('dialog[open]').forEach((openDialog) => openDialog.close());
            dialog.showModal();
        });
    });

    document.querySelectorAll('[data-close-dialog]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const dialog = trigger.closest('dialog');
            if (dialog) dialog.close();
        });
    });

    // Close Dialog by clicking backdrop
    document.querySelectorAll('dialog.agent-admin__dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
    });

    // Bridge from details to edit modal directly
    document.querySelectorAll('[data-trigger-edit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const editDialog = document.getElementById(btn.dataset.triggerEdit);
            if (editDialog) {
                setTimeout(() => { editDialog.showModal(); }, 50);
            }
        });
    });

    // Form saving loading state
    document.querySelectorAll('.agent-admin__edit-form').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[data-saving-label]') || document.querySelector(`[form="${form.id}"][data-saving-label]`);
            if (!button) return;
            button.disabled = true;
            button.textContent = button.dataset.savingLabel || 'Saving...';
        });
    });

    // Toast Alert Helper
    let toastTimeout;
    function showToast(message, type = 'success') {
        const toast = document.getElementById('agent-toast');
        const msgEl = document.getElementById('agent-toast-message');
        const iconEl = toast.querySelector('.agent-toast-icon');
        
        msgEl.textContent = message;
        
        toast.classList.remove('agent-admin__toast--success', 'agent-admin__toast--error');
        toast.classList.add(`agent-admin__toast--${type}`);
        
        iconEl.textContent = type === 'success' ? '✓' : '✕';
        
        toast.classList.add('is-visible');
        
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 4000);
    }

    // Dynamic AJAX confirmation action flow
    const confirmModal = document.getElementById('confirm-modal');
    const confirmText = document.getElementById('confirm-text');
    const confirmAgentName = document.getElementById('confirm-agent-name');
    const confirmAgentEmail = document.getElementById('confirm-agent-email');
    const confirmConfirmBtn = document.getElementById('btn-confirm-action');
    
    let activeActionContext = null;

    // Delegate dynamic actions
    document.addEventListener('click', (event) => {
        const actionBtn = event.target.closest('[data-action]');
        if (!actionBtn) return;

        event.preventDefault();
        
        const action = actionBtn.dataset.action;
        const url = actionBtn.dataset.url;
        const container = actionBtn.closest('[data-agent-id]');
        if (!container) return;

        const agentId = container.dataset.agentId;
        const agentName = container.dataset.agentName;
        const agentEmail = container.dataset.agentEmail;
        const agentSlug = container.dataset.agentSlug;

        let question = 'Are you sure you want to perform this action?';
        switch (action) {
            case 'approve':
                question = 'Approve and publish this agent profile to the public directory?';
                break;
            case 'suspend':
                question = 'Suspend this agent profile and remove it from the public directory?';
                break;
            case 'feature':
                question = 'Feature this agent profile?';
                break;
            case 'unfeature':
                question = 'Remove featured status from this agent profile?';
                break;
            case 'reactivate':
                question = 'Reactivate this suspended agent profile?';
                break;
        }

        // Setup confirm modal content
        confirmText.textContent = question;
        confirmAgentName.textContent = agentName;
        confirmAgentEmail.textContent = agentEmail;

        activeActionContext = {
            btn: actionBtn,
            url: url,
            agentId: agentId,
            agentSlug: agentSlug,
            action: action
        };

        confirmModal.showModal();
    });

    // Close confirm handlers
    document.querySelectorAll('[data-close-confirm]').forEach((btn) => {
        btn.addEventListener('click', () => {
            confirmModal.close();
            activeActionContext = null;
        });
    });

    // Confirm click action handler
    confirmConfirmBtn.addEventListener('click', () => {
        if (!activeActionContext) return;

        const context = activeActionContext;
        confirmModal.close();

        // Show spinner / loading on the action button
        const originalText = context.btn.textContent;
        context.btn.disabled = true;
        context.btn.textContent = '...';

        fetch(context.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Response error');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Status updated successfully.', 'success');
                
                // Update KPI status counts
                if (data.counts) {
                    for (const [key, val] of Object.entries(data.counts)) {
                        const countEl = document.querySelector(`[data-status-count="${key}"]`);
                        if (countEl) countEl.textContent = Number(val).toLocaleString();
                    }
                }

                // Update status badge and action group in all containers matching this agent id (table row and mobile card)
                const targetContainers = document.querySelectorAll(`[data-agent-id="${context.agentId}"]`);
                targetContainers.forEach(container => {
                    const row = container.closest('tr');
                    const card = container.closest('.agent-admin__mobile-card');
                    
                    let badge;
                    if (row) {
                        badge = row.querySelector('.agent-admin__status');
                    } else if (card) {
                        badge = card.querySelector('.agent-admin__status');
                    }

                    if (badge) {
                        badge.textContent = data.status_label;
                        badge.className = `agent-admin__status agent-admin__status--${data.profile_status}`;
                    }

                    // Re-render action button group
                    const actionGroup = container.querySelector('.agent-admin__status-actions-group');
                    if (actionGroup) {
                        actionGroup.innerHTML = renderActionButtons(data.profile_status, context.agentSlug);
                    }

                    // Toggle view public profile link
                    const viewLink = container.querySelector('.data-view-link');
                    if (viewLink) {
                        if (data.profile_status === 'approved' || data.profile_status === 'published' || data.profile_status === 'featured') {
                            viewLink.style.display = 'inline-flex';
                        } else {
                            viewLink.style.display = 'none';
                        }
                    }
                });
            } else {
                context.btn.disabled = false;
                context.btn.textContent = originalText;
                showToast(data.message || 'Action failed.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            context.btn.disabled = false;
            context.btn.textContent = originalText;
            showToast('A network error occurred. Please try again.', 'error');
        })
        .finally(() => {
            activeActionContext = null;
        });
    });

    // Helper to generate new buttons layout in DOM
    function renderActionButtons(status, slug) {
        const baseUrl = `{{ url('admin/agent-profiles') }}/${slug}`;
        if (status === 'draft') {
            return `<button type="button" class="button agent-admin__button-success" data-action="approve" data-url="${baseUrl}/publish">Approve</button>`;
        } else if (status === 'approved' || status === 'published') {
            return `
                <button type="button" class="button agent-admin__button-feature" data-action="feature" data-url="${baseUrl}/feature">Feature</button>
                <button type="button" class="button agent-admin__button-danger" data-action="suspend" data-url="${baseUrl}/suspend">Suspend</button>
            `;
        } else if (status === 'featured') {
            return `
                <button type="button" class="button button--ghost-blue" data-action="unfeature" data-url="${baseUrl}/publish">Unfeature</button>
                <button type="button" class="button agent-admin__button-danger" data-action="suspend" data-url="${baseUrl}/suspend">Suspend</button>
            `;
        } else if (status === 'suspended') {
            return `<button type="button" class="button agent-admin__button-success" data-action="reactivate" data-url="${baseUrl}/publish">Reactivate</button>`;
        }
        return '';
    }
});
</script>
@endpush
