@extends('layouts.app')

@section('content')
<section class="page-hero dashboard-page-hero dashboard-page-hero--agent">
    <div class="container page-hero__content">
        <span class="eyebrow">Agent Messages</span>
        <h1>Review every property and profile inquiry sent to you</h1>
        <p>Website inquiries from your public agent page and listing pages now route directly into this inbox so nothing gets buried in the general contact form.</p>
    </div>
</section>

<section class="section dashboard-page agent-portal-shell">
    <div class="container agent-portal-grid">
        @include('pages.dashboards.partials.agent-portal-sidebar')

        <div class="agent-portal-main">
            <div class="cockpit-kpi-row">
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Total Messages</span>
                    <strong>{{ $totalMessagesCount }}</strong>
                    <p>All inquiries saved to your inbox</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Unread</span>
                    <strong>{{ $unreadMessagesCount }}</strong>
                    <p>Messages waiting for first review</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Listings</span>
                    <strong>{{ $activeListingCount }}</strong>
                    <p>Active listings generating conversations</p>
                </article>
                <article class="cockpit-kpi-card">
                    <span class="eyebrow">Assigned Leads</span>
                    <strong>{{ $agentStats['leads_received'] }}</strong>
                    <p>Current lead workload beside inbox traffic</p>
                </article>
            </div>

            <section class="cockpit-table-card agent-portal-section">
                <div class="agent-portal-section__header">
                    <div>
                        <span class="eyebrow">Inbox</span>
                        <h2>All direct inquiries</h2>
                    </div>
                </div>

                <div class="agent-portal-message-grid">
                    @forelse($messages as $message)
                        <article class="agent-message-card">
                            <div class="agent-message-card__header">
                                <div>
                                    <strong>{{ $message->subject ?: 'New inquiry' }}</strong>
                                    <span>{{ $message->name }} &middot; {{ $message->email }}</span>
                                </div>
                                <span class="status-pill status-pill--{{ $message->message_status === 'new' ? 'assigned' : 'qualified' }}">
                                    {{ ucfirst(str_replace('_', ' ', $message->message_status)) }}
                                </span>
                            </div>

                            <div class="agent-message-card__meta">
                                <span><strong>Phone:</strong> {{ $message->phone ?: 'Not provided' }}</span>
                                <span><strong>Source:</strong> {{ ucfirst(str_replace('_', ' ', $message->source ?: 'website')) }}</span>
                                <span><strong>Property:</strong> {{ $message->property?->title ?: 'Direct profile inquiry' }}</span>
                                <span><strong>Received:</strong> {{ $message->created_at->format('M j, Y g:i A') }}</span>
                            </div>

                            <p>{{ $message->message }}</p>

                            <div class="agent-message-card__actions">
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
                        </article>
                    @empty
                        <div class="cockpit-empty-state">
                            <h3>No inbound messages yet</h3>
                            <p class="text-gray-500">Once a user contacts you from a listing or agent profile page, the message will land here automatically.</p>
                        </div>
                    @endforelse
                </div>

                <div class="agent-portal-pagination">
                    {{ $messages->links() }}
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
