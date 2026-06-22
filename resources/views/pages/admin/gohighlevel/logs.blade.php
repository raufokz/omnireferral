@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GHL Logs & Monitoring')
@section('dashboard_description', 'Inspect GoHighLevel webhook deliveries, onboarding submissions, and sync activity.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.debug') }}" class="button button--ghost-blue">Debug</a>
    <a href="{{ route('admin.ghl.testing') }}" class="button">Test Tools</a>
@endsection

@section('content')
<div class="workspace-stack">

    {{-- Filters --}}
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Search webhook events</h2>
        <form method="GET" action="{{ route('admin.ghl.logs') }}">
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Event type</span>
                    <select name="event_type">
                        <option value="">All events</option>
                        @foreach($eventTypes as $type)
                            <option value="{{ $type }}" {{ ($filters['eventType'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">Any</option>
                        <option value="processed" {{ ($filters['status'] ?? '') === 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="pending"   {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Search (email / remote ID)</span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search…">
                </label>
            </div>
            <div class="workspace-actions" style="margin-top:.75rem;">
                <button type="submit" class="button">Apply</button>
                <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Reset</a>
            </div>
        </form>
    </section>

    {{-- Webhook events --}}
    <section class="workspace-card">
        <span class="eyebrow">Webhook Events</span>
        <h2>GoHighLevel deliveries <span style="font-size:.9rem; font-weight:400; color:var(--color-text-muted,#6b7280);">({{ $webhooks->total() }} total)</span></h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem;">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event</th>
                        <th>Remote ID</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($webhooks as $wh)
                    <tr>
                        <td data-label="ID"><strong>#{{ $wh->id }}</strong></td>
                        <td data-label="Event"><code>{{ $wh->event }}</code></td>
                        <td data-label="Remote ID">{{ $wh->remote_id ?: '—' }}</td>
                        <td data-label="Status"><span class="workspace-pill {{ $wh->statusBadgeClass() }}">{{ $wh->statusLabel() }}</span></td>
                        <td data-label="Received">{{ $wh->created_at?->format('M j, Y g:i A') }}</td>
                        <td data-label="" style="display:flex; gap:.5rem; flex-wrap:wrap;">
                            <a href="{{ route('admin.webhook-events.show', $wh->id) }}" class="button button--ghost-blue" style="font-size:.8rem; padding:.25rem .6rem;">Detail</a>
                            @if(! $wh->processed_at)
                            <form action="{{ route('admin.ghl.retry', $wh->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="button" style="font-size:.8rem; padding:.25rem .6rem;">Retry</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><div class="workspace-empty">No GHL webhook events found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $webhooks->links() }}</div>
    </section>

    {{-- Onboarding logs --}}
    <section class="workspace-card" id="onboarding">
        <span class="eyebrow">Onboarding Sync Logs</span>
        <h2>GHL onboarding submissions <span style="font-size:.9rem; font-weight:400; color:var(--color-text-muted,#6b7280);">({{ $onboardingLogs->total() }} total)</span></h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem; overflow-x:auto;">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>ID / When</th>
                        <th>Contact</th>
                        <th>Form</th>
                        <th>Webhook</th>
                        <th>User</th>
                        <th>Profile</th>
                        <th>Onboarded</th>
                        <th>Portal</th>
                        <th>Email</th>
                        <th>Error</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($onboardingLogs as $log)
                    @php
                        $contactName = $log->contact_name ?: ($log->user?->name ?: data_get($log->payload, 'name'));
                        $contactEmail = $log->triggered_by ?: $log->user?->email;
                        $contactPhone = $log->contact_phone ?: data_get($log->payload, 'phone');
                        $ghlId = $log->ghl_contact_id ?: ($log->user?->ghl_contact_id ?: data_get($log->payload, 'contact_id'));
                        $formName = $log->form_name ?: data_get($log->payload, 'form_name');
                        $formId = $log->form_id ?: data_get($log->payload, 'form_id');
                        $onboarded = (bool) $log->user?->onboarding_completed_at;
                        $eligible = $log->user && filled($log->user->email) && $log->user->onboarding_completed_at && in_array($log->user->status, ['active','approved'], true);
                        $emailStatus = $log->email_status ?? ($log->email_sent ? 'sent' : 'pending');
                        $emailPill = match($emailStatus) {
                            'sent' => 'workspace-pill--green',
                            'failed' => 'workspace-pill--red',
                            'skipped' => '',
                            default => 'workspace-pill--orange',
                        };
                    @endphp
                    <tr>
                        <td data-label="ID / When">
                            <strong>#{{ $log->id }}</strong><br>
                            <span style="font-size:.75rem; color:var(--color-text-muted,#9ca3af);">{{ $log->created_at?->format('M j, Y g:i A') }}</span>
                        </td>
                        <td data-label="Contact">
                            @if($log->user)
                                <a href="{{ route('admin.users.show', $log->user) }}" style="color:inherit; font-weight:600;">{{ $contactName ?: '—' }}</a>
                                <span class="workspace-pill" style="font-size:.7rem;">{{ $log->user->role }}</span>
                            @else
                                <strong>{{ $contactName ?: '—' }}</strong>
                                <span class="workspace-pill workspace-pill--orange" style="font-size:.7rem;">no user</span>
                            @endif
                            <div style="font-size:.75rem; color:var(--color-text-muted,#6b7280); margin-top:2px;">
                                {{ $contactEmail ?: 'no email' }}<br>
                                {{ $contactPhone ?: '—' }}
                                @if($ghlId)<br><span title="GoHighLevel Contact ID">GHL: <code>{{ $ghlId }}</code></span>@endif
                            </div>
                        </td>
                        <td data-label="Form">
                            {{ $formName ?: '—' }}
                            @if($formId)<br><span style="font-size:.72rem; color:var(--color-text-muted,#9ca3af);">ID: {{ $formId }}</span>@endif
                        </td>
                        <td data-label="Webhook">
                            <span class="workspace-pill {{ $log->processed_at ? 'workspace-pill--green' : 'workspace-pill--orange' }}">
                                {{ $log->processed_at ? 'Received' : 'Pending' }}
                            </span>
                        </td>
                        <td data-label="User">
                            @if($log->user_action)
                                <span class="workspace-pill workspace-pill--green" style="font-size:.7rem;">{{ ucfirst($log->user_action) }}</span>
                            @else
                                <span style="color:var(--color-text-muted,#9ca3af);">—</span>
                            @endif
                        </td>
                        <td data-label="Profile">
                            @if($log->profile_action)
                                <span class="workspace-pill workspace-pill--green" style="font-size:.7rem;">{{ ucfirst($log->profile_action) }}</span>
                            @else
                                <span style="color:var(--color-text-muted,#9ca3af);">—</span>
                            @endif
                        </td>
                        <td data-label="Onboarded">
                            <span class="workspace-pill {{ $onboarded ? 'workspace-pill--green' : 'workspace-pill--orange' }}">{{ $onboarded ? 'Yes' : 'No' }}</span>
                        </td>
                        <td data-label="Portal">
                            <span class="workspace-pill {{ $log->portal_access_enabled ? 'workspace-pill--green' : 'workspace-pill--orange' }}">{{ $log->portal_access_enabled ? 'Yes' : 'No' }}</span>
                        </td>
                        <td data-label="Email">
                            <span class="workspace-pill {{ $emailPill }}">{{ ucfirst($emailStatus) }}</span>
                            @if($log->email_sent_at)<br><span style="font-size:.7rem; color:var(--color-text-muted,#9ca3af);">{{ $log->email_sent_at->format('M j, g:i A') }}</span>@endif
                        </td>
                        <td data-label="Error">
                            @if($log->error_message)
                                <span style="font-size:.72rem; color:#b91c1c;" title="{{ $log->error_message }}">{{ \Illuminate\Support\Str::limit($log->error_message, 60) }}</span>
                            @else
                                <span style="color:var(--color-text-muted,#9ca3af);">—</span>
                            @endif
                        </td>
                        <td data-label="Actions" style="white-space:nowrap;">
                            @if($log->user)
                            <button onclick="resendPortalEmail({{ $log->id }})"
                                    class="button button--ghost-blue"
                                    style="font-size:.8rem; padding:.25rem .6rem;"
                                    @unless($eligible) title="Not eligible — click to see why" @endunless>
                                Resend Email
                            </button>
                            @unless($eligible)
                                <div style="font-size:.68rem; color:#b45309; margin-top:3px;">
                                    @if(blank($log->user->email)) Missing email
                                    @elseif(! $log->user->onboarding_completed_at) Onboarding not completed
                                    @elseif(! in_array($log->user->status, ['active','approved'], true)) User still pending
                                    @endif
                                </div>
                            @endunless
                            @else
                                <span style="color:var(--color-text-muted,#9ca3af);">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11"><div class="workspace-empty">No onboarding logs found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $onboardingLogs->links() }}</div>
    </section>

</div>

<script>
function resendPortalEmail(logId) {
    if (!confirm('Resend portal access email for this user?')) {
        return;
    }

    const button = event.target;
    button.disabled = true;
    button.textContent = 'Sending...';

    fetch(`{{ route('admin.ghl.resend-email', 0) }}`.replace('0', logId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message + (data.reasons ? '\n\nReasons:\n' + data.reasons.join('\n') : ''));
        }
    })
    .catch(error => {
        alert('Failed to resend email: ' + error.message);
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Resend Email';
    });
}
</script>
@endsection
