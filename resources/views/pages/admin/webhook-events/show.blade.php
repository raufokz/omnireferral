@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Webhook event #' . $event->id)
@section('dashboard_description', ($event->provider ?: 'provider') . ' — ' . ($event->event ?: 'event'))

@section('dashboard_actions')
    <a href="{{ route('admin.webhook-events.index') }}" class="button button--ghost-blue">Back</a>
@endsection

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="workspace-form-grid">
            <div class="workspace-field workspace-field--full">
                <span class="eyebrow">Metadata</span>
                <div class="workspace-property__meta">
                    <strong>Provider:</strong> {{ $event->provider }}<br>
                    <strong>Event:</strong> {{ $event->event }}<br>
                    <strong>Remote ID:</strong> {{ $event->remote_id ?: '—' }}<br>
                    <strong>IP:</strong> {{ $event->ip_address ?: '—' }}<br>
                    <strong>Processed at:</strong> {{ $event->processed_at?->toDateTimeString() ?: '—' }}<br>
                    <strong>Created at:</strong> {{ $event->created_at?->toDateTimeString() ?: '—' }}
                </div>
            </div>
        </div>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Headers</span>
        <pre style="white-space:pre-wrap;">{{ json_encode($event->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Payload</span>
        <pre style="white-space:pre-wrap;">{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </section>
</div>
@endsection

