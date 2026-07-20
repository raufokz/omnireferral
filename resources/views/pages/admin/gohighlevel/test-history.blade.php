@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'GHL Onboarding Test History')
@section('dashboard_description', 'Search, filter, and inspect all past onboarding test submissions.')

@section('dashboard_actions')
    <a href="{{ route('admin.ghl.index') }}" class="button button--ghost-blue">Overview</a>
    <a href="{{ route('admin.ghl.test-panel') }}" class="button">Test Panel</a>
    <a href="{{ route('admin.ghl.webhook-debugger') }}" class="button button--ghost-blue">Webhook Debugger</a>
@endsection

@push('styles')
<style>
.ghl-stats-bar { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:.6rem; margin-bottom:1rem; }
.ghl-stats-bar .stat { background:var(--color-surface-subtle,#f8fafc); border:1px solid var(--color-border,#e5e7eb); border-radius:8px; padding:.6rem .85rem; text-align:center; }
.ghl-stats-bar .stat span { display:block; font-size:.68rem; color:var(--color-text-muted,#6b7280); text-transform:uppercase; letter-spacing:.03em; }
.ghl-stats-bar .stat strong { font-size:1.2rem; font-weight:700; color:var(--color-text,#111827); }
.ghl-stats-bar .stat--completed strong { color:#15803d; }
.ghl-stats-bar .stat--failed strong { color:#b91c1c; }
.ghl-stats-bar .stat--processing strong { color:#c2410c; }
.ghl-history-filters { display:flex; gap:.5rem; flex-wrap:wrap; align-items:end; margin-bottom:1rem; }
.ghl-history-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.ghl-history-table th { text-align:left; padding:.5rem .6rem; font-weight:600; color:var(--color-text-muted,#6b7280); font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--color-border,#e5e7eb); white-space:nowrap; }
.ghl-history-table td { padding:.5rem .6rem; border-bottom:1px solid var(--color-border,#e5e7eb); vertical-align:middle; }
.ghl-history-table tr:hover td { background:var(--color-surface-subtle,#f8fafc); }
.ghl-history-table .ghl-pill { font-size:.6rem; font-weight:700; text-transform:uppercase; padding:.15rem .4rem; border-radius:3px; }
.ghl-history-table .ghl-pill--completed { background:#bbf7d0; color:#166534; }
.ghl-history-table .ghl-pill--failed { background:#fecaca; color:#991b1b; }
.ghl-history-table .ghl-pill--processing { background:#fed7aa; color:#9a3412; }
.ghl-empty-state { padding:3rem 2rem; text-align:center; color:var(--color-text-muted,#9ca3af); }
.ghl-empty-state strong { display:block; font-size:1.05rem; margin-bottom:.25rem; color:var(--color-text,#374151); }
.ghl-filter-input { padding:.35rem .6rem; border:1px solid var(--color-border,#e5e7eb); border-radius:5px; font-size:.8rem; }
.ghl-filter-input:focus { outline:2px solid var(--color-orange,#f97316); outline-offset:-1px; border-color:transparent; }
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- Stats --}}
    <section class="workspace-card">
        <span class="eyebrow">Summary</span>
        <h2>Test submission statistics</h2>
        <div class="ghl-stats-bar">
            <div class="stat"><span>Total</span><strong>{{ $stats['total'] }}</strong></div>
            <div class="stat stat--completed"><span>Completed</span><strong>{{ $stats['completed'] }}</strong></div>
            <div class="stat stat--failed"><span>Failed</span><strong>{{ $stats['failed'] }}</strong></div>
            <div class="stat stat--processing"><span>Processing</span><strong>{{ $stats['processing'] }}</strong></div>
        </div>
    </section>

    {{-- Filters + table --}}
    <section class="workspace-card">
        <span class="eyebrow">Test Results</span>
        <h2>All submissions</h2>

        <form method="GET" action="{{ route('admin.ghl.test.history') }}" class="ghl-history-filters">
            <label style="display:flex;flex-direction:column;gap:2px;font-size:.72rem;color:var(--color-text-muted,#6b7280);">
                Search
                <input type="text" name="search" class="ghl-filter-input" placeholder="Email, GHL ID, name…" value="{{ $filters['search'] }}" style="width:200px;">
            </label>
            <label style="display:flex;flex-direction:column;gap:2px;font-size:.72rem;color:var(--color-text-muted,#6b7280);">
                Status
                <select name="status" class="ghl-filter-input" style="width:130px;">
                    <option value="">All</option>
                    <option value="completed" @selected($filters['status'] === 'completed')>Completed</option>
                    <option value="failed" @selected($filters['status'] === 'failed')>Failed</option>
                    <option value="processing" @selected($filters['status'] === 'processing')>Processing</option>
                </select>
            </label>
            <label style="display:flex;flex-direction:column;gap:2px;font-size:.72rem;color:var(--color-text-muted,#6b7280);">
                From
                <input type="date" name="from" class="ghl-filter-input" value="{{ $filters['from'] }}">
            </label>
            <label style="display:flex;flex-direction:column;gap:2px;font-size:.72rem;color:var(--color-text-muted,#6b7280);">
                To
                <input type="date" name="to" class="ghl-filter-input" value="{{ $filters['to'] }}">
            </label>
            <button type="submit" class="button" style="font-size:.78rem;">Filter</button>
            @if($filters['search'] || $filters['status'] || $filters['from'] || $filters['to'])
                <a href="{{ route('admin.ghl.test.history') }}" class="button button--ghost-blue" style="font-size:.78rem;">Clear</a>
            @endif
        </form>

        @if($tests->isEmpty())
            <div class="ghl-empty-state">
                <strong>No tests found</strong>
                <span>@if($filters['search'] || $filters['status'] || $filters['from'] || $filters['to'])Try adjusting your filters.@else Submit your first onboarding test from the Test Panel.@endif</span>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="ghl-history-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>GHL Contact</th>
                            <th>User</th>
                            <th>Method</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tests as $t)
                        <tr>
                            <td><code>#{{ $t->id }}</code></td>
                            <td><strong>{{ $t->email }}</strong></td>
                            <td>{{ $t->role }}</td>
                            <td><span class="ghl-pill ghl-pill--{{ $t->status }}">{{ $t->status }}</span></td>
                            <td><code style="font-size:.72rem;">{{ $t->ghl_contact_id ?: '—' }}</code></td>
                            <td><code style="font-size:.72rem;">{{ $t->user_id ? '#'.$t->user_id : '—' }}</code></td>
                            <td><span style="font-size:.72rem;color:var(--color-text-muted,#6b7280);">{{ Str::replace('_', ' ', $t->form_submission_method ?: '—') }}</span></td>
                            <td style="white-space:nowrap;font-size:.75rem;color:var(--color-text-muted,#6b7280);">{{ $t->created_at?->format('M j, Y g:i A') }}</td>
                            <td>
                                <a href="{{ route('admin.ghl.test-panel') }}#load-{{ $t->id }}" class="button button--ghost-blue" style="font-size:.7rem;padding:.2rem .55rem;" onclick="event.preventDefault();openTest({{ $t->id }})">Reopen</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="workspace-pagination" style="margin-top:1rem;">{{ $tests->links() }}</div>
        @endif
    </section>

</div>

<script>
function openTest(testId) {
    const url = '{{ route('admin.ghl.test-panel') }}';
    sessionStorage.setItem('ghl_reopen_test', testId);
    window.location.href = url;
}
</script>
@endsection
