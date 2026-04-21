@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', 'Message Inbox')
@section('dashboard_description', 'All profile and listing inquiries are organized on a dedicated page for clear follow-up.')

@section('dashboard_actions')
    <a href="{{ route('dashboard.agent') }}" class="button button--ghost-blue">Overview</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Total Messages</span>
            <strong>{{ number_format($totalMessagesCount) }}</strong>
            <span>All inbox conversations</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Unread</span>
            <strong>{{ number_format($unreadMessagesCount) }}</strong>
            <span>Need immediate attention</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Active Listings</span>
            <strong>{{ number_format($activeListingCount) }}</strong>
            <span>Listings that can receive messages</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Assigned Leads</span>
            <strong>{{ number_format($agentStats['leads_received']) }}</strong>
            <span>Related lead workload</span>
        </article>
    </section>

    <section class="workspace-card">
        @if($messages->isEmpty())
            <div class="workspace-empty">No inbox messages yet.</div>
        @else
            <ul class="workspace-list">
                @foreach($messages as $message)
                    <li>
                        <div class="workspace-actions" style="justify-content: space-between; align-items: flex-start;">
                            <div>
                                <strong>{{ $message->subject ?: 'New inquiry' }}</strong>
                                <small>{{ $message->name }} · {{ $message->email }}</small>
                            </div>
                            <span class="status-pill status-pill--{{ $message->message_status === 'new' ? 'assigned' : 'qualified' }}">
                                {{ ucfirst(str_replace('_', ' ', $message->message_status)) }}
                            </span>
                        </div>

                        <div class="workspace-pill-row" style="margin-top: 0.5rem;">
                            <span class="workspace-pill">Phone: {{ $message->phone ?: 'Not provided' }}</span>
                            <span class="workspace-pill">Source: {{ ucfirst(str_replace('_', ' ', $message->source ?: 'website')) }}</span>
                            <span class="workspace-pill workspace-pill--accent">Received: {{ $message->created_at->format('M j, Y g:i A') }}</span>
                        </div>

                        <p style="margin-top: 0.55rem;">{{ $message->message }}</p>

                        <div class="workspace-actions" style="margin-top: 0.7rem;">
                            @if($message->property)
                                <a href="{{ route('properties.show', $message->property) }}" class="button button--ghost-blue">Open Listing</a>
                            @endif
                            <form action="{{ route('agent.messages.status', $message) }}" method="POST">
                                @csrf
                                <select name="message_status" onchange="this.form.submit()" aria-label="Update message status">
                                    @foreach(['new', 'read', 'replied', 'archived'] as $status)
                                        <option value="{{ $status }}" {{ $message->message_status === $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="workspace-pagination">
            {{ $messages->links() }}
        </div>
    </section>
</div>
@endsection
