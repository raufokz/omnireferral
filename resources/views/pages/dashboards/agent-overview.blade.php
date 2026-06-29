@extends('layouts.dashboard')

@php
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', trim($agentUser->name))[0] ?? $agentUser->name;

    $statusColors = [
        'featured'  => ['pill' => 'status-pill--qualified', 'label' => 'Featured'],
        'published' => ['pill' => 'status-pill--new',       'label' => 'Published'],
        'draft'     => ['pill' => 'status-pill--neutral',   'label' => 'Draft'],
        'suspended' => ['pill' => 'status-pill--critical',  'label' => 'Suspended'],
    ];
    $profileStatus = $statusColors[$agentProfile->profile_status ?? 'draft'] ?? $statusColors['draft'];

    $profileFields = [
        'brokerage' => filled($agentProfile->brokerage_name),
        'license'   => filled($agentProfile->license_number),
        'bio'       => filled($agentProfile->bio),
        'specialty' => filled($agentProfile->specialties),
        'city'      => filled($agentProfile->service_city),
        'headshot'  => filled($agentProfile->headshot),
    ];
    $profileComplete = (int) round(collect($profileFields)->filter()->count() / count($profileFields) * 100);

    $pipelineMax = max(1, collect($pipeline)->max('count'));
    $pipelineColors = ['#0b3668', '#1d5fa0', '#ff6b00', '#16a34a'];
@endphp

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', $greeting . ', ' . $firstName)
@section('dashboard_description', now()->format('l, F j, Y') . ' · ' . ($activePlan?->displayName() ?: 'No active plan'))

@section('dashboard_actions')
    @if($agentProfile->isPublicVisible())
        <a href="{{ route('agents.profile', $agentProfile) }}" class="button button--ghost-blue" target="_blank" rel="noopener">
            Public Profile
        </a>
    @else
        <a href="{{ route('agent.profile') }}" class="button button--ghost-blue">Complete Profile</a>
    @endif
    <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">Lead Queue</a>
    <a href="{{ route('agent.listings.index') }}" class="button">+ New Listing</a>
@endsection

@push('styles')
<style>
.agent-kpi-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 12px;
    display: grid;
    place-items: center;
    margin-bottom: 0.6rem;
    flex-shrink: 0;
}
.agent-kpi-icon svg { width: 1.2rem; height: 1.2rem; }
.agent-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.agent-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.agent-kpi-icon--teal   { background: rgba(14,165,233,0.12); color: #0369a1; }
.agent-kpi-icon--violet { background: rgba(109,93,252,0.12); color: #5145cd; }
.agent-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }

.agent-pipeline-bar { height: 10px; border-radius: 999px; background: #e8edf4; overflow: hidden; margin-top: 0.4rem; }
.agent-pipeline-bar__fill { height: 100%; border-radius: 999px; transition: width 0.6s cubic-bezier(.16,1,.3,1); }

.agent-profile-health {
    display: grid;
    gap: 0.9rem;
}
.agent-completeness-bar { height: 8px; border-radius: 999px; background: #e8edf4; overflow: hidden; margin-top: 0.35rem; }
.agent-completeness-bar__fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #0b3668, #ff6b00); transition: width 0.6s; }

.agent-checklist { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.4rem; }
.agent-checklist li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.83rem;
    color: var(--dash-shell-muted);
}
.agent-checklist li.done { color: #15803d; }
.agent-checklist li .check-icon { width: 1rem; height: 1rem; flex-shrink: 0; }

.agent-lead-intent {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.18rem 0.5rem;
    border-radius: 999px;
}
.agent-lead-intent--buyer  { background: rgba(14,165,233,0.12); color: #0369a1; }
.agent-lead-intent--seller { background: rgba(255,107,0,0.12); color: #c2410c; }

.agent-message-card {
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    border-radius: 14px;
    padding: 0.85rem;
    display: grid;
    gap: 0.4rem;
    transition: border-color 0.2s;
}
.agent-message-card:hover { border-color: #0b3668; }
.agent-message-card--unread { border-left: 3px solid #ff6b00; }
.agent-message-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; }
.agent-message-snippet { font-size: 0.83rem; color: var(--dash-shell-muted); margin: 0; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }

.agent-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}
.agent-quick-action {
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    border-radius: 14px;
    padding: 1rem;
    display: grid;
    gap: 0.3rem;
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.2s, border-color 0.2s, transform 0.2s;
}
.agent-quick-action:hover {
    border-color: #0b3668;
    box-shadow: 0 8px 20px rgba(11,54,104,0.12);
    transform: translateY(-2px);
}
.agent-quick-action strong { font-size: 0.9rem; color: var(--dash-shell-text); }
.agent-quick-action span { font-size: 0.78rem; color: var(--dash-shell-muted); }
.agent-quick-action-icon { width: 1.8rem; height: 1.8rem; margin-bottom: 0.4rem; color: #0b3668; }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- KPI Row --}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="{{ $agentStats['response_rate'] }} pace">
            <div class="agent-kpi-icon agent-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span>Assigned Leads</span>
            <strong>{{ number_format($agentStats['leads_received']) }}</strong>
            <span>{{ number_format($agentStats['closed_leads']) }} closed deals</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="Contact pace">
            <div class="agent-kpi-icon agent-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <span>Response Rate</span>
            <strong>{{ $agentStats['response_rate'] }}</strong>
            <span>Lead progression pace</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="{{ $unreadMessagesCount }} unread">
            <div class="agent-kpi-icon agent-kpi-icon--teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <span>Inbox Messages</span>
            <strong>{{ number_format($totalMessagesCount) }}</strong>
            <span>{{ number_format($unreadMessagesCount) }} need attention</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--violet" data-trend="Capacity">
            <div class="agent-kpi-icon agent-kpi-icon--violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <span>Listing Slots Left</span>
            <strong>{{ number_format($remainingListingSlots) }}</strong>
            <span>{{ $listingLimitLabel }}</span>
        </article>

    </section>

    {{-- Pipeline + Profile Health --}}
    <section class="workspace-grid workspace-grid--2">

        <article class="workspace-card">
            <span class="eyebrow">Pipeline</span>
            <h2>Lead Stage Distribution</h2>
            <div class="workspace-stack" style="margin-top: 0.75rem; gap: 0.85rem;">
                @foreach($pipeline as $index => $stage)
                    @php $pct = $pipelineMax > 0 ? round(($stage['count'] / $pipelineMax) * 100) : 0; @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.3rem;">
                            <strong style="font-size:0.88rem;">{{ $stage['label'] }}</strong>
                            <span style="font-size:0.78rem; font-weight:700; color:var(--dash-shell-muted);">
                                {{ number_format($stage['count']) }}
                                @if($agentStats['leads_received'] > 0)
                                    &middot; {{ round(($stage['count'] / $agentStats['leads_received']) * 100) }}%
                                @endif
                            </span>
                        </div>
                        <div class="agent-pipeline-bar">
                            <div class="agent-pipeline-bar__fill" style="width: {{ $pct }}%; background: {{ $pipelineColors[$index] ?? '#0b3668' }};"></div>
                        </div>
                    </div>
                @endforeach
                @if($agentStats['leads_received'] === 0)
                    <div class="workspace-empty">No leads assigned yet. Your pipeline will appear once leads are matched to you.</div>
                @endif
            </div>
        </article>

        <article class="workspace-card agent-profile-health">
            <div>
                <span class="eyebrow">Profile Health</span>
                <h2>Profile &amp; Plan Status</h2>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <strong style="font-size:0.88rem;">Profile Completeness</strong>
                    <span style="font-size:0.82rem; font-weight:700; color: {{ $profileComplete >= 80 ? '#15803d' : ($profileComplete >= 50 ? '#d97706' : '#dc2626') }};">{{ $profileComplete }}%</span>
                </div>
                <div class="agent-completeness-bar" style="margin-top:0.4rem;">
                    <div class="agent-completeness-bar__fill" style="width: {{ $profileComplete }}%;"></div>
                </div>
            </div>

            <ul class="agent-checklist">
                <li class="{{ $profileFields['brokerage'] ? 'done' : '' }}">
                    @if($profileFields['brokerage'])
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" style="color:#e2e8f0;"><circle cx="10" cy="10" r="8"/></svg>
                    @endif
                    Brokerage name
                </li>
                <li class="{{ $profileFields['license'] ? 'done' : '' }}">
                    @if($profileFields['license'])
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" style="color:#e2e8f0;"><circle cx="10" cy="10" r="8"/></svg>
                    @endif
                    License number
                </li>
                <li class="{{ $profileFields['bio'] ? 'done' : '' }}">
                    @if($profileFields['bio'])
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" style="color:#e2e8f0;"><circle cx="10" cy="10" r="8"/></svg>
                    @endif
                    Bio written
                </li>
                <li class="{{ $profileFields['headshot'] ? 'done' : '' }}">
                    @if($profileFields['headshot'])
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" style="color:#e2e8f0;"><circle cx="10" cy="10" r="8"/></svg>
                    @endif
                    Profile headshot
                </li>
                <li class="{{ $profileFields['specialty'] ? 'done' : '' }}">
                    @if($profileFields['specialty'])
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" style="color:#e2e8f0;"><circle cx="10" cy="10" r="8"/></svg>
                    @endif
                    Specialties listed
                </li>
            </ul>

            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                    <strong style="font-size:0.88rem;">Profile Visibility</strong>
                    <span class="status-pill {{ $profileStatus['pill'] }}">{{ $profileStatus['label'] }}</span>
                </div>
                @if($agentProfile->isPublicVisible())
                    <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; color:#15803d;">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Your profile is live and discoverable in the agent directory.
                    </div>
                @else
                    <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; color:var(--dash-shell-muted);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Complete your profile so the admin team can publish it to the directory.
                    </div>
                @endif
            </div>

            @if($agentStats['score'])
                <div style="display:flex; justify-content:space-between; align-items:center; padding-top:0.5rem; border-top:1px solid var(--dash-shell-border);">
                    <strong style="font-size:0.88rem;">Directory Rating</strong>
                    <div style="display:flex; align-items:center; gap:0.35rem;">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="#f59e0b"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <strong style="font-size:1rem; color:var(--dash-shell-text);">{{ $agentStats['score'] }}</strong>
                        <span style="font-size:0.78rem; color:var(--dash-shell-muted);">/ 5.0</span>
                    </div>
                </div>
            @endif

            @if($subscription ?? null)
                <div style="padding-top:0.6rem; border-top:1px solid var(--dash-shell-border);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.3rem;">
                        <strong style="font-size:0.82rem;">Subscription</strong>
                        <span class="status-pill status-pill--{{ $subscription->is_active ? 'green' : 'grey' }}">
                            {{ $subscription->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    @if($currentMonthQuota ?? null)
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.82rem;">
                            <span>Monthly Leads: {{ $currentMonthQuota->assigned_count }}/{{ $currentMonthQuota->monthly_quota }}</span>
                            <span style="color:var(--dash-shell-muted);">{{ $currentMonthQuota->remaining_count }} remaining</span>
                        </div>
                    @endif
                    @if($subscription->ends_at)
                        <div style="font-size:0.75rem; color:var(--dash-shell-muted); margin-top:0.2rem;">
                            Expires: {{ $subscription->ends_at->format('M j, Y') }}
                        </div>
                    @endif
                    @if($subscription->payment_provider === 'gohighlevel')
                        <div style="font-size:0.75rem; color:var(--dash-shell-muted);">
                            Paid via GoHighLevel
                        </div>
                    @endif
                </div>
            @else
                <div style="padding-top:0.6rem; border-top:1px solid var(--dash-shell-border);">
                    <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.82rem; color:#dc2626;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Your subscription is not active. Please contact support or complete your payment.
                    </div>
                </div>
            @endif

            <div class="workspace-actions">
                <a href="{{ route('agent.profile') }}" class="button button--ghost-blue">Edit Profile</a>
                @if($activePlan)
                    <span class="workspace-pill workspace-pill--accent">{{ $activePlan->displayName() }}</span>
                @else
                    <a href="{{ route('pricing') }}" class="button button--ghost-blue">Upgrade Plan</a>
                @endif
            </div>
        </article>

    </section>

    {{-- Recent Leads + Recent Messages --}}
    <section class="workspace-grid workspace-grid--2">

        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.75rem;">
                <div>
                    <span class="eyebrow">Recent Leads</span>
                    <h2>Latest Assigned</h2>
                </div>
                <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">View All</a>
            </div>

            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Intent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td data-label="Lead">
                                    <strong>{{ $lead->name }}</strong>
                                    <div class="workspace-property__meta">
                                        {{ $lead->phone ?: 'Phone pending' }}
                                        @if($lead->zip_code)
                                            · {{ $lead->zip_code }}
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Intent">
                                    <span class="agent-lead-intent agent-lead-intent--{{ $lead->intent ?? 'buyer' }}">
                                        {{ ucfirst($lead->intent ?? 'Buyer') }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="workspace-empty" style="padding:1.4rem;">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#cbd5e1; margin:0 auto 0.5rem; display:block;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        No leads assigned yet.<br>
                                        <small style="font-size:0.78rem;">Leads matched to your profile will appear here.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content:space-between; margin-bottom:0.75rem;">
                <div>
                    <span class="eyebrow">Messages &amp; Enquiries</span>
                    <h2>Inbox &amp; Listing Enquiries</h2>
                </div>
                <a href="{{ route('agent.messages.index') }}" class="button button--ghost-blue">Open Inbox</a>
            </div>

            <div style="display:grid; gap:0.6rem;">
                @forelse($recentMessages as $message)
                    <div class="agent-message-card {{ $message->message_status === 'new' ? 'agent-message-card--unread' : '' }}">
                        <div class="agent-message-meta">
                            <strong style="font-size:0.9rem; margin-right:auto;">{{ $message->subject ?: 'New inquiry' }}</strong>
                            <span class="status-pill status-pill--{{ $message->message_status === 'new' ? 'assigned' : 'neutral' }}" style="font-size:0.68rem;">
                                {{ ucfirst($message->message_status ?? 'new') }}
                            </span>
                        </div>
                        <p class="workspace-property__meta" style="font-size:0.83rem; margin:0;">
                            {{ $message->name }}
                            @if($message->phone)
                                · {{ $message->phone }}
                            @endif
                        </p>
                        @if($message->message)
                            <p class="agent-message-snippet">{{ $message->message }}</p>
                        @endif
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.2rem;">
                            <span style="font-size:0.73rem; color:var(--dash-shell-muted);">{{ $message->created_at?->diffForHumans() }}</span>
                            @if($message->property)
                                <a href="{{ route('properties.show', $message->property) }}" style="font-size:0.75rem; color:#0b3668; font-weight:600; text-decoration:none;">
                                    View Listing →
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="workspace-empty" style="padding:1.4rem;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#cbd5e1; margin:0 auto 0.5rem; display:block;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        No messages yet.<br>
                        <small style="font-size:0.78rem;">Inquiries from your listings and profile page will appear here.</small>
                    </div>
                @endforelse
            </div>
        </article>

    </section>

    {{-- Quick Actions --}}
    <article class="workspace-card">
        <span class="eyebrow">Quick Actions</span>
        <h2>Next Steps</h2>
        <div class="agent-quick-actions" style="margin-top:0.75rem;">
            <a href="{{ route('agent.leads.index') }}" class="agent-quick-action">
                <svg class="agent-quick-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <strong>Work Lead Queue</strong>
                <span>Update {{ $agentStats['leads_received'] }} assigned {{ Str::plural('lead', $agentStats['leads_received']) }} while intent is fresh.</span>
            </a>
            <a href="{{ route('agent.messages.index') }}" class="agent-quick-action">
                <svg class="agent-quick-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <strong>Clear Inbox</strong>
                <span>{{ $unreadMessagesCount }} unread {{ Str::plural('message', $unreadMessagesCount) }} {{ $unreadMessagesCount === 1 ? 'needs' : 'need' }} a response.</span>
            </a>
            <a href="{{ route('agent.listings.index') }}" class="agent-quick-action">
                <svg class="agent-quick-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <strong>Manage Listings</strong>
                <span>{{ $activeListingCount }} live · {{ $pendingReviewCount }} pending review · {{ $remainingListingSlots }} {{ Str::plural('slot', $remainingListingSlots) }} remaining.</span>
            </a>
            <a href="{{ route('agent.profile') }}" class="agent-quick-action">
                <svg class="agent-quick-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <strong>Update Profile</strong>
                <span>Profile is {{ $profileComplete }}% complete. A strong profile ranks higher in the agent directory.</span>
            </a>
            <a href="{{ route('pricing') }}" class="agent-quick-action">
                <svg class="agent-quick-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <strong>Review Packages</strong>
                <span>{{ $activePlan ? 'Current plan: ' . $activePlan->displayName() . '. Compare upgrades.' : 'No active plan. Review options to access more leads.' }}</span>
            </a>
        </div>
    </article>

</div>
@endsection
