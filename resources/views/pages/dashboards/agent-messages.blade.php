@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Message Inbox')
@section('dashboard_description', 'All profile and listing inquiries organized for clear, fast follow-up.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Overview</a>
@endsection

@push('styles')
<style>
.agent-kpi-icon {
    width: 2.4rem;
    height: 2.4rem;
    border-radius: 11px;
    display: grid;
    place-items: center;
    margin-bottom: 0.5rem;
}
.agent-kpi-icon svg { width: 1.1rem; height: 1.1rem; }
.agent-kpi-icon--blue   { background: rgba(11,54,104,0.10); color: #0b3668; }
.agent-kpi-icon--orange { background: rgba(255,107,0,0.13); color: #c2410c; }
.agent-kpi-icon--teal   { background: rgba(14,165,233,0.12); color: #0369a1; }
.agent-kpi-icon--green  { background: rgba(22,163,74,0.12); color: #15803d; }

.inbox-filter-row {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.inbox-filter-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.38rem 0.85rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    border: 1px solid var(--dash-shell-border);
    background: var(--dash-shell-panel-soft);
    color: var(--dash-shell-muted);
    cursor: pointer;
    text-decoration: none;
    transition: background 0.18s, color 0.18s, border-color 0.18s;
}
.inbox-filter-chip.is-active,
.inbox-filter-chip:hover { background: rgba(11,54,104,0.09); color: #0b3668; border-color: #0b3668; }

.inbox-message-card {
    background: var(--dash-shell-panel);
    border: 1px solid var(--dash-shell-border);
    border-radius: 16px;
    padding: 1.1rem;
    display: grid;
    gap: 0.6rem;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.inbox-message-card:hover { border-color: #0b3668; box-shadow: 0 6px 18px rgba(11,54,104,0.09); }
.inbox-message-card--unread { border-left: 3px solid #ff6b00; }

.inbox-message-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}
.inbox-message-from {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.inbox-message-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0b3668 0%, #1d5fa0 100%);
    display: grid;
    place-items: center;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    flex-shrink: 0;
    font-family: 'Sora', sans-serif;
}
.inbox-message-body { font-size: 0.85rem; color: var(--dash-shell-muted); line-height: 1.5; }
.inbox-message-snippet {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.inbox-message-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.inbox-status-form select {
    appearance: none;
    border: 1px solid var(--dash-shell-border);
    background: #fff;
    border-radius: 8px;
    padding: 0.35rem 0.55rem;
    font-size: 0.78rem;
    cursor: pointer;
    color: var(--dash-shell-text);
}
.inbox-source-chip {
    display: inline-flex;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.18rem 0.5rem;
    border-radius: 999px;
    background: rgba(11,54,104,0.08);
    color: #0b3668;
}
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- KPI Row --}}
    <section class="workspace-grid workspace-grid--4">

        <article class="workspace-card workspace-kpi" data-trend="All time">
            <div class="agent-kpi-icon agent-kpi-icon--blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <span>Total Messages</span>
            <strong>{{ number_format($totalMessagesCount) }}</strong>
            <span>All inbox conversations</span>
        </article>

        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="Action needed">
            <div class="agent-kpi-icon agent-kpi-icon--orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </div>
            <span>Unread</span>
            <strong>{{ number_format($unreadMessagesCount) }}</strong>
            <span>Need immediate attention</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="From listings">
            <div class="agent-kpi-icon agent-kpi-icon--teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <span>Active Listings</span>
            <strong>{{ number_format($activeListingCount) }}</strong>
            <span>Listings receiving inquiries</span>
        </article>

        <article class="workspace-card workspace-kpi" data-trend="Lead context">
            <div class="agent-kpi-icon agent-kpi-icon--green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
            </div>
            <span>Assigned Leads</span>
            <strong>{{ number_format($agentStats['leads_received']) }}</strong>
            <span>Related lead workload</span>
        </article>

    </section>

    {{-- Messages Section --}}
    <section class="workspace-card">
        @if($messages->isEmpty())
            <div class="workspace-empty" style="padding:2.5rem;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#cbd5e1; margin:0 auto 0.75rem; display:block;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <strong style="display:block; margin-bottom:0.35rem; color:var(--dash-shell-text); font-size:1.05rem;">Inbox is empty</strong>
                <p style="font-size:0.85rem; color:var(--dash-shell-muted); max-width:340px; margin:0 auto 1rem;">
                    Inquiries sent from your listing pages and public agent profile will appear here as soon as they arrive.
                </p>
                <div class="workspace-actions" style="justify-content:center;">
                    <a href="{{ route('agent.listings.index') }}" class="button button--ghost-blue">Add a Listing</a>
                    <a href="{{ route('agent.profile') }}" class="button button--ghost-blue">Improve Profile</a>
                </div>
            </div>
        @else
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.9rem;">
                <div>
                    <span class="eyebrow">Inbox</span>
                    <h2>All Messages</h2>
                </div>
                @if($unreadMessagesCount > 0)
                    <span class="status-pill status-pill--assigned">{{ $unreadMessagesCount }} unread</span>
                @endif
            </div>

            <div style="display:grid; gap:0.75rem;">
                @foreach($messages as $message)
                    @php
                        $initials = strtoupper(substr($message->name ?? 'U', 0, 1));
                        $isUnread = $message->message_status === 'new';
                    @endphp
                    <div class="inbox-message-card {{ $isUnread ? 'inbox-message-card--unread' : '' }}">

                        <div class="inbox-message-header">
                            <div class="inbox-message-from">
                                <div class="inbox-message-avatar">{{ $initials }}</div>
                                <div>
                                    <strong style="font-size:0.92rem; display:block; margin-bottom:0.1rem;">{{ $message->name }}</strong>
                                    <div style="display:flex; gap:0.4rem; flex-wrap:wrap; align-items:center;">
                                        @if($message->email)
                                            <span style="font-size:0.78rem; color:var(--dash-shell-muted);">{{ $message->email }}</span>
                                        @endif
                                        @if($message->phone)
                                            <span style="font-size:0.78rem; color:var(--dash-shell-muted);">· {{ $message->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:0.4rem; flex-shrink:0;">
                                <span class="status-pill status-pill--{{ $isUnread ? 'assigned' : 'neutral' }}" style="font-size:0.68rem;">
                                    {{ ucfirst($message->message_status ?? 'new') }}
                                </span>
                                <span style="font-size:0.73rem; color:var(--dash-shell-muted); white-space:nowrap;">
                                    {{ $message->created_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>

                        @if($message->subject)
                            <strong style="font-size:0.9rem; display:block;">{{ $message->subject }}</strong>
                        @endif

                        @if($message->message)
                            <p class="inbox-message-body inbox-message-snippet">{{ $message->message }}</p>
                        @endif

                        <div class="inbox-message-footer">
                            <div style="display:flex; gap:0.45rem; flex-wrap:wrap; align-items:center;">
                                @if($message->source)
                                    <span class="inbox-source-chip">{{ ucfirst(str_replace('_', ' ', $message->source)) }}</span>
                                @endif
                                @if($message->property)
                                    <a href="{{ route('properties.show', $message->property) }}" class="inbox-source-chip" style="text-decoration:none; color:#0b3668;">
                                        {{ Str::limit($message->property->title, 30) }} →
                                    </a>
                                @endif
                            </div>
                            <form action="{{ route('agent.messages.status', $message) }}" method="POST" class="inbox-status-form">
                                @csrf
                                <select name="message_status" onchange="this.form.submit()" aria-label="Update status for message from {{ $message->name }}">
                                    @foreach(['new' => 'Mark New', 'read' => 'Mark Read', 'replied' => 'Mark Replied', 'archived' => 'Archive'] as $value => $label)
                                        <option value="{{ $value }}" {{ $message->message_status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>

            <div class="workspace-pagination">
                {{ $messages->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
