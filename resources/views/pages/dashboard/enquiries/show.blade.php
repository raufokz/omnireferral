@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Workspace')
@section('dashboard_title', 'Enquiry conversation')
@section('dashboard_description', $enquiry->property?->title ?? 'Listing enquiry')

@section('dashboard_actions')
    <a href="{{ route('dashboard.enquiries.index') }}" class="button button--ghost-blue">All enquiries</a>
    @if($enquiry->property)
        <a href="{{ route('properties.show', $enquiry->property) }}" class="button" target="_blank" rel="noopener">View listing</a>
    @endif
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">You</span>
            <h2>
                @if(auth()->id() === (int) $enquiry->receiver_user_id)
                    Listed by (you)
                @else
                    Your enquiry
                @endif
            </h2>
            <p class="workspace-property__meta">Status: <strong>{{ ucfirst($enquiry->status) }}</strong></p>
        </article>
        <article class="workspace-card">
            <span class="eyebrow">Other party</span>
            @if(auth()->id() === (int) $enquiry->receiver_user_id)
                <h2>{{ $enquiry->sender_name }}</h2>
                <p class="workspace-property__meta">{{ $enquiry->sender_email }}</p>
            @else
                <h2>{{ $enquiry->receiver?->name ?? 'Property owner' }}</h2>
                <p class="workspace-property__meta">{{ $enquiry->receiver?->email }}</p>
            @endif
        </article>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Messages</span>
        <h2>Thread</h2>
        @include('partials.enquiry-thread', [
            'enquiry' => $enquiry,
            'replyUrl' => $replyUrl,
            'canReply' => $canReply,
            'statusUrl' => $canClose ? $statusUrl : null,
            'statusMode' => ($canClose && $enquiry->status !== 'closed') ? 'close' : null,
        ])
    </section>
</div>
@endsection
