@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', $isStaffView ? 'Staff Operations Overview' : 'Admin Command Overview')
@section('dashboard_description', 'Monitor lead flow, listing moderation, and cross-team operations from a single modern control center.')

@section('dashboard_actions')
    <a href="{{ route('admin.leads.index') }}" class="button">Lead Registry</a>
    <a href="{{ route('admin.testimonials.index') }}" class="button button--ghost-blue">Testimonials</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Total Leads</span>
            <strong>{{ number_format($stats['leads'] ?? 0) }}</strong>
            <span>All pipeline records</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Partners</span>
            <strong>{{ number_format($stats['realtors'] ?? 0) }}</strong>
            <span>Active realtor profiles</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Pending Listings</span>
            <strong>{{ number_format($stats['pendingListings'] ?? 0) }}</strong>
            <span>User uploads awaiting review</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Estimated Revenue</span>
            <strong>${{ number_format($stats['estimatedRevenue'] ?? 0) }}</strong>
            <span>Recent lead-package projection</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card workspace-kpi">
            <span>Pending sign-ups</span>
            <strong>{{ number_format($stats['pendingAccounts'] ?? 0) }}</strong>
            <span>Buyer, seller, and agent registrations awaiting activation</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>User-submitted listings</span>
            <strong>{{ number_format($stats['userSubmittedListingsTotal'] ?? 0) }}</strong>
            <span>Agent &amp; seller uploads (all moderation states)</span>
        </article>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Account activation</span>
        <h2>Pending registrations</h2>
        <ul class="workspace-list">
            @forelse($pendingAccounts as $account)
                <li>
                    <strong>{{ $account->name }}</strong>
                    <small>{{ ucfirst($account->role) }} · {{ $account->email }} · {{ $account->created_at?->format('M j, Y g:i A') }}</small>
                    <div class="workspace-actions" style="margin-top: 0.6rem;">
                        <form method="POST" action="{{ route('admin.users.review', $account) }}">
                            @csrf
                            <input type="hidden" name="decision" value="approve">
                            <button type="submit" class="button">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.review', $account) }}">
                            @csrf
                            <input type="hidden" name="decision" value="suspend">
                            <button type="submit" class="button button--ghost-blue">Suspend</button>
                        </form>
                    </div>
                </li>
            @empty
                <li>
                    <strong>No accounts waiting for activation</strong>
                    <small>New registrations will appear here until an admin approves them.</small>
                </li>
            @endforelse
        </ul>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Lead Pipeline</span>
            <h2>Stage Distribution</h2>
            <div class="workspace-stack">
                @foreach($pipelineHealth as $stage)
                    <div>
                        <div class="workspace-actions" style="justify-content: space-between;">
                            <strong>{{ $stage['label'] }}</strong>
                            <small>{{ number_format($stage['count']) }}</small>
                        </div>
                        <div style="height: 8px; border-radius: 999px; background: #e8edf4; margin-top: 0.45rem;">
                            <div style="height:100%; border-radius:999px; width: {{ $stage['percent'] }}%; background: linear-gradient(90deg, #0b3668, #ff6b00);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Team Queues</span>
            <h2>Operational Pressure Points</h2>
            <ul class="workspace-list">
                @foreach($teamQueues as $queue)
                    <li>
                        <strong>{{ $queue['team'] }} · {{ number_format($queue['count']) }}</strong>
                        <small>{{ $queue['copy'] }}</small>
                    </li>
                @endforeach
            </ul>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-actions" style="justify-content: space-between; margin-bottom: 0.7rem;">
            <div>
                <span class="eyebrow">Latest Leads</span>
                <h2>Recent Registry Records</h2>
            </div>
            <a href="{{ route('admin.leads.index') }}" class="button button--ghost-blue">Open Full Registry</a>
        </div>

        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Intent</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLeads as $lead)
                        <tr>
                            <td>
                                <strong>{{ $lead->name }}</strong>
                                <div class="workspace-property__meta">{{ $lead->email ?: 'No email' }}</div>
                            </td>
                            <td>
                                <strong>{{ ucfirst($lead->intent ?: 'Unknown') }}</strong>
                                <div class="workspace-property__meta">{{ $lead->zip_code ?: 'No ZIP' }}</div>
                            </td>
                            <td>{{ strtoupper($lead->package_type ?: 'N/A') }}</td>
                            <td>
                                <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug((string) $lead->status, '_') }}">
                                    {{ $lead->statusLabel() }}
                                </span>
                            </td>
                            <td>{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="workspace-empty">No recent leads yet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="workspace-card">
            <span class="eyebrow">Listing review</span>
            <h2>User-submitted listings</h2>
            <p style="margin: 0 0 0.75rem; color: #64748b; font-size: 0.9rem;">Includes pending, approved, and rejected uploads from agent and seller workspaces (latest 25).</p>
            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Listing</th>
                            <th>Source</th>
                            <th>Moderation</th>
                            <th>Owner</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userSubmittedListings as $property)
                            <tr>
                                <td>
                                    <strong>{{ $property->title }}</strong>
                                    <div class="workspace-property__meta">{{ $property->location }}</div>
                                </td>
                                <td>{{ $property->source }}</td>
                                <td>
                                    <span class="status-pill status-pill--{{ \Illuminate\Support\Str::slug($property->approval_status, '_') }}">
                                        {{ $property->approvalStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    {{ $property->owner?->name ?? optional($property->realtorProfile?->user)->name ?? '—' }}
                                </td>
                                <td>
                                    <div class="workspace-actions" style="flex-wrap: wrap; gap: 0.35rem;">
                                        <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Preview</a>
                                        @if($property->approval_status === \App\Models\Property::APPROVAL_PENDING)
                                            <form method="POST" action="{{ route('admin.properties.review', $property) }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="decision" value="approve">
                                                <button type="submit" class="button">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.properties.review', $property) }}" style="display: inline;">
                                                @csrf
                                                <input type="hidden" name="decision" value="reject">
                                                <button type="submit" class="button button--ghost-blue">Reject</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="workspace-empty">No user-submitted listings yet.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </section>

    <section class="workspace-card">
            <span class="eyebrow">Listing Inquiries</span>
            <h2>Recent Contact Flow</h2>
            <ul class="workspace-list">
                @forelse($recentListingMessages as $message)
                    <li>
                        <strong>{{ $message->subject ?: 'Listing inquiry' }}</strong>
                        <small>{{ ucfirst($message->role ?: 'guest') }} · {{ $message->created_at?->format('M j, Y g:i A') }}</small>
                        <small>
                            Routed to {{ $message->recipient?->name ?: 'OmniReferral team' }}
                        </small>
                    </li>
                @empty
                    <li>
                        <strong>No listing inquiries yet</strong>
                        <small>Contact activity will populate this feed once users start messaging.</small>
                    </li>
                @endforelse
            </ul>
    </section>
</div>
@endsection
