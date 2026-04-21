@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Agent Overview')
@section('dashboard_description', 'Keep leads, listings, and message response in sync with a modern page-based workspace.')

@section('dashboard_actions')
    <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">Lead Queue</a>
    <a href="{{ route('agent.listings.index') }}" class="button">Listings</a>
@endsection

@section('content')
@php
    $pipelineMax = max(1, collect($pipeline)->max('count'));
@endphp

<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Assigned Leads</span>
            <strong>{{ number_format($agentStats['leads_received']) }}</strong>
            <span>Total opportunities assigned to you</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Response Rate</span>
            <strong>{{ $agentStats['response_rate'] }}</strong>
            <span>Lead progression pace</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Unread Messages</span>
            <strong>{{ number_format($unreadMessagesCount) }}</strong>
            <span>Inbox items awaiting action</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Listing Slots Left</span>
            <strong>{{ number_format($remainingListingSlots) }}</strong>
            <span>{{ $listingLimitLabel }}</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Pipeline Health</span>
            <h2>Lead Stage Distribution</h2>
            <div class="workspace-stack">
                @foreach($pipeline as $stage)
                    <div>
                        <div class="workspace-actions" style="justify-content: space-between;">
                            <strong>{{ $stage['label'] }}</strong>
                            <small>{{ number_format($stage['count']) }}</small>
                        </div>
                        <div style="height: 8px; border-radius: 999px; background: #e8edf4; margin-top: 0.45rem;">
                            <div style="height:100%; border-radius:999px; width: {{ ($stage['count'] / $pipelineMax) * 100 }}%; background: linear-gradient(90deg, #0b3668, #ff6b00);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Current Capacity</span>
            <h2>Listing And Message Load</h2>
            <ul class="workspace-list">
                <li>
                    <strong>Active Listings</strong>
                    <small>{{ number_format($activeListingCount) }} live and {{ number_format($pendingReviewCount) }} pending review.</small>
                </li>
                <li>
                    <strong>Total Messages</strong>
                    <small>{{ number_format($totalMessagesCount) }} inbound inquiries from listings and profile pages.</small>
                </li>
                <li>
                    <strong>Property Saves</strong>
                    <small>{{ number_format($totalFavoritesReceived) }} favorites across your listing portfolio.</small>
                </li>
            </ul>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content: space-between; margin-bottom: 0.7rem;">
                <div>
                    <span class="eyebrow">Recent Leads</span>
                    <h2>Latest Assigned Opportunities</h2>
                </div>
                <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">View All</a>
            </div>

            <div class="workspace-table-wrap">
                <table class="workspace-table">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>Status</th>
                            <th>Package</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td>
                                    <strong>{{ $lead->name }}</strong>
                                    <div class="workspace-property__meta">{{ $lead->phone ?: 'No phone' }}</div>
                                </td>
                                <td>
                                    <span class="status-pill status-pill--{{ $lead->statusTone() }}">{{ $lead->statusLabel() }}</span>
                                </td>
                                <td>{{ strtoupper($lead->package_type ?: 'N/A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="workspace-empty">No assigned leads yet.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="workspace-card">
            <div class="workspace-actions" style="justify-content: space-between; margin-bottom: 0.7rem;">
                <div>
                    <span class="eyebrow">Recent Messages</span>
                    <h2>Latest Inbox Activity</h2>
                </div>
                <a href="{{ route('agent.messages.index') }}" class="button button--ghost-blue">Open Inbox</a>
            </div>
            <ul class="workspace-list">
                @forelse($recentMessages as $message)
                    <li>
                        <strong>{{ $message->subject ?: 'New inquiry' }}</strong>
                        <small>{{ $message->name }} · {{ $message->created_at?->format('M j, Y g:i A') }}</small>
                    </li>
                @empty
                    <li>
                        <strong>No direct messages yet</strong>
                        <small>Inquiries from listing and profile pages will appear here.</small>
                    </li>
                @endforelse
            </ul>
        </article>
    </section>
</div>
@endsection
