@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Onboarding Users')
@section('dashboard_description', 'All users who completed GoHighLevel onboarding — track status, resend portal emails, and manage accounts.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.logs') }}" class="button button--ghost-blue">Logs</a>
    <a href="{{ route('admin.ghl.manage-users') }}" class="button button--ghost-blue" style="opacity:.6; pointer-events:none;">Onboarding Users</a>
    <a href="{{ route('admin.ghl.testing') }}" class="button button--ghost-blue">Test Tools</a>
@endsection

@push('styles')
<style>
.ghl-stat-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.75rem; margin-bottom:1.5rem; }
.ghl-stat-card { background:var(--color-surface,#fff); border:1px solid var(--color-border,#e5e7eb); border-radius:8px; padding:1rem; }
.ghl-stat-value { font-size:1.5rem; font-weight:700; color:var(--color-text,#0f172a); line-height:1.2; }
.ghl-stat-label { font-size:.75rem; color:var(--color-text-muted,#6b7280); margin-top:2px; }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- Stats --}}
    <div class="ghl-stat-grid">
        <div class="ghl-stat-card"><div class="ghl-stat-value">{{ $stats['total'] }}</div><div class="ghl-stat-label">Total Onboarded</div></div>
        <div class="ghl-stat-card"><div class="ghl-stat-value" style="color:#f59e0b;">{{ $stats['pending'] }}</div><div class="ghl-stat-label">Pending Approval</div></div>
        <div class="ghl-stat-card"><div class="ghl-stat-value" style="color:#22c55e;">{{ $stats['active'] }}</div><div class="ghl-stat-label">Active</div></div>
        <div class="ghl-stat-card"><div class="ghl-stat-value" style="color:#ef4444;">{{ $stats['suspended'] }}</div><div class="ghl-stat-label">Suspended</div></div>
        <div class="ghl-stat-card"><div class="ghl-stat-value">{{ $stats['email_sent_today'] }}</div><div class="ghl-stat-label">Emails Sent Today</div></div>
        <div class="ghl-stat-card"><div class="ghl-stat-value">{{ $stats['webhooks_today'] }}</div><div class="ghl-stat-label">Webhooks Today</div></div>
    </div>

    {{-- Filters + Search --}}
    <section class="workspace-card">
        <span class="eyebrow">Filters</span>
        <h2>Search &amp; filter onboarded users</h2>
        <form method="GET" action="{{ route('admin.ghl.manage-users') }}" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:end; margin-top:.75rem;">
            <div class="workspace-field" style="flex:2; min-width:200px;">
                <label for="search">Search name, email, or phone</label>
                <input type="text" name="search" id="search" placeholder="Search..." value="{{ request('search') }}" autocomplete="off">
            </div>
            <div class="workspace-field" style="min-width:140px;">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                </select>
            </div>
            <button type="submit" class="button">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.ghl.manage-users') }}" class="button button--ghost-blue">Clear</a>
            @endif
        </form>
    </section>

    {{-- Users table --}}
    <section class="workspace-card">
        <span class="eyebrow">Users</span>
        <h2>All GHL onboarded agents <span style="font-size:.9rem; font-weight:400; color:var(--color-text-muted,#6b7280);">({{ $users->total() }} total)</span></h2>
        <div class="workspace-table-wrap" style="margin-top:.75rem; overflow-x:auto;">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>ID / When</th>
                        <th>Name / Contact</th>
                        <th>Location</th>
                        <th>GHL Contact</th>
                        <th>Status</th>
                        <th>Plan</th>
                        <th>Onboarded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    @php
                        $profile = $user->realtorProfile;
                    @endphp
                    <tr>
                        <td data-label="ID / When">
                            <strong>#{{ $user->id }}</strong><br>
                            <span style="font-size:.75rem; color:var(--color-text-muted,#9ca3af);">{{ $user->created_at?->format('M j, Y g:i A') }}</span>
                        </td>
                        <td data-label="Name / Contact">
                            <a href="{{ route('admin.users.show', $user) }}" style="color:inherit; font-weight:600;">{{ $user->name ?: '—' }}</a>
                            <span class="workspace-pill" style="font-size:.7rem;">{{ $user->role }}</span>
                            <div style="font-size:.75rem; color:var(--color-text-muted,#6b7280); margin-top:2px;">
                                {{ $user->email }}<br>@if($user->phone){{ $user->phone }}@endif
                            </div>
                        </td>
                        <td data-label="Location">
                            <span style="font-size:.85rem;">
                                {{ collect([$user->city, $user->state])->filter()->implode(', ') ?: '—' }}
                            </span>
                            <div style="font-size:.75rem; color:var(--color-text-muted,#6b7280);">
                                @if($profile)
                                    {{ $profile->brokerage_name ?: '—' }}
                                @endif
                            </div>
                        </td>
                        <td data-label="GHL Contact">
                            <code style="font-size:.75rem;">{{ $user->ghl_contact_id ?: '—' }}</code>
                        </td>
                        <td data-label="Status">
                            @php
                                $statusPill = match($user->status) {
                                    'active' => 'workspace-pill--green',
                                    'pending' => 'workspace-pill--orange',
                                    'suspended' => 'workspace-pill--red',
                                    default => '',
                                };
                            @endphp
                            <span class="workspace-pill {{ $statusPill }}">{{ ucfirst($user->status) }}</span>
                            @if($profile)
                                <span style="display:block; font-size:.7rem; margin-top:2px; color:var(--color-text-muted,#6b7280);">
                                    Profile: {{ $profile->profile_status }}
                                </span>
                            @endif
                        </td>
                        <td data-label="Plan">
                            @if($user->currentPlan)
                                <span style="font-size:.85rem;">{{ $user->currentPlan->name }}</span>
                            @else
                                <span style="color:var(--color-text-muted,#9ca3af);">—</span>
                            @endif
                        </td>
                        <td data-label="Onboarded">
                            <span style="font-size:.8rem;">{{ $user->onboarding_completed_at?->format('M j, Y') ?: '—' }}</span>
                        </td>
                        <td data-label="Actions" style="display:flex; gap:.5rem; flex-wrap:wrap;">
                            <a href="{{ route('admin.users.show', $user) }}" class="button button--ghost-blue" style="font-size:.8rem; padding:.25rem .6rem;">View</a>
                            <button type="button" class="button send-email-btn" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" style="font-size:.8rem; padding:.25rem .6rem;">Send Email</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="workspace-empty">No onboarded users found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="workspace-pagination">{{ $users->links() }}</div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.send-email-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const userId = this.dataset.userId;
        const userName = this.dataset.userName;
        const originalText = this.textContent;

        if (!confirm(`Send portal access email to "${userName}"? A new secure token will be generated and emailed.`)) {
            return;
        }

        this.textContent = 'Sending...';
        this.disabled = true;

        try {
            const response = await fetch('{{ url('admin/onboarding/users') }}/' + userId + '/send-email', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (result.ok) {
                alert(result.message);
            } else {
                alert('Failed: ' + (result.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        } finally {
            this.textContent = originalText;
            this.disabled = false;
        }
    });
});
</script>
@endpush
