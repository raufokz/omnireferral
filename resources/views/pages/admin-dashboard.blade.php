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
            <span>Awaiting moderation</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Estimated Revenue</span>
            <strong>${{ number_format($stats['estimatedRevenue'] ?? 0) }}</strong>
            <span>Recent lead-package projection</span>
        </article>
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

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Listing Review</span>
            <h2>Pending Property Moderation</h2>
            <ul class="workspace-list">
                @forelse($pendingProperties as $property)
                    <li>
                        <strong>{{ $property->title }}</strong>
                        <small>{{ $property->location }} · {{ optional(optional($property->realtorProfile)->user)->name ?? 'No owner' }}</small>
                        <div class="workspace-actions" style="margin-top: 0.6rem;">
                            <a href="{{ route('properties.show', $property) }}" class="button button--ghost-blue">Preview</a>
                            <form method="POST" action="{{ route('admin.properties.review', $property) }}">
                                @csrf
                                <input type="hidden" name="decision" value="approve">
                                <button type="submit" class="button">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.properties.review', $property) }}">
                                @csrf
                                <input type="hidden" name="decision" value="reject">
                                <button type="submit" class="button button--ghost-blue">Reject</button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li>
                        <strong>No listings waiting for review</strong>
                        <small>New submissions will appear here automatically.</small>
                    </li>
                @endforelse
            </ul>
        </article>

        <article class="workspace-card">
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
        </article>
    </section>
</div>
@endsection
