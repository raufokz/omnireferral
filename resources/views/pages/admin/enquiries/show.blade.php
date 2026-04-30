@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', 'Enquiry #{{ $enquiry->id }}')
@section('dashboard_description', 'Conversation thread, participants, and listing context.')

@section('dashboard_actions')
    <a href="{{ route('admin.enquiries.index') }}" class="button button--ghost-blue">All enquiries</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Sender</span>
            <h2>{{ $enquiry->sender_name }}</h2>
            <p class="workspace-property__meta">{{ $enquiry->sender_email }}</p>
            <p class="workspace-property__meta">{{ $enquiry->sender_phone ?: 'No phone' }}</p>
            @if($enquiry->sender)
                <p class="workspace-property__meta">Linked account: {{ $enquiry->sender->name }} ({{ $enquiry->sender->email }})</p>
            @else
                <p class="workspace-property__meta">Guest enquiry (no linked account)</p>
            @endif
            <p class="workspace-property__meta">Submitted {{ $enquiry->created_at?->format('M j, Y g:i A') }}</p>
        </article>
        <article class="workspace-card">
            <span class="eyebrow">Listed by (receiver)</span>
            <h2>{{ $enquiry->receiver?->name ?? '—' }}</h2>
            <p class="workspace-property__meta">{{ $enquiry->receiver?->email }}</p>
            <p class="workspace-property__meta">Status: <strong>{{ ucfirst($enquiry->status) }}</strong></p>
            @if($enquiry->contact?->source)
                <p class="workspace-property__meta">Source: {{ $enquiry->contact->source }}</p>
            @endif
        </article>
    </section>

    @if($enquiry->property)
        <section class="workspace-card">
            <span class="eyebrow">Property</span>
            <h2>{{ $enquiry->property->title }}</h2>
            <p class="workspace-property__meta">{{ $enquiry->property->location }} · {{ $enquiry->property->zip_code }}</p>
            <div class="workspace-actions">
                <a href="{{ route('properties.show', $enquiry->property) }}" class="button button--ghost-blue" target="_blank" rel="noopener">Open listing</a>
                <a href="{{ route('admin.properties.edit', $enquiry->property) }}" class="button button--ghost-blue">Edit in admin</a>
            </div>
        </section>
    @endif

    <section class="workspace-card">
        <span class="eyebrow">Conversation</span>
        <h2>Thread</h2>
        @include('partials.enquiry-thread', [
            'enquiry' => $enquiry,
            'replyUrl' => $replyUrl,
            'canReply' => $canReply,
            'statusUrl' => $statusUrl,
            'statusMode' => 'admin',
        ])
    </section>
</div>
@endsection
