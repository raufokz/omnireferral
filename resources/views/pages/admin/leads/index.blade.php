@extends('layouts.app')

@section('content')
@php
    $workspaceAvatar = $workspaceUser?->avatar
        ? asset('storage/' . ltrim($workspaceUser->avatar, '/'))
        : asset('images/realtors/3.png');
    $workspaceTitle = 'Lead Registry Management';
    $workspaceCopy = 'Review qualification, assignment, and package stages across the entire OmniReferral ecosystem from one synchronized workspace.';
    $workspaceHighlights = [
        ['label' => 'Total Leads', 'value' => number_format($stats['leads'] ?? 0)],
        ['label' => 'Qualified', 'value' => number_format($summary['qualified'] ?? 0)],
        ['label' => 'Pending Review', 'value' => number_format($stats['pendingListings'] ?? 0)],
        ['label' => 'Rejected', 'value' => number_format($summary['rejected'] ?? 0)],
    ];
@endphp

<section class="or-dashboard or-dashboard--admin">
    <div class="or-dashboard__shell">
        <aside class="or-dashboard__sidebar">
            <div class="or-dashboard__brand">
                <img src="{{ asset('images/omnireferral-logo.png') }}" alt="OmniReferral logo">
                <div class="or-dashboard__brand-copy">
                    <strong>{{ $isStaffView ? 'Staff Workspace' : 'Admin Workspace' }}</strong>
                    <span>OmniReferral operations desk</span>
                </div>
            </div>

            <nav class="or-dashboard__nav" aria-label="Operations workspace navigation">
                <a href="{{ route('admin.dashboard') }}">
                    <span>Overview</span>
                    <small>Lead flow, listing reviews, and team health</small>
                </a>
                <a class="is-active" href="{{ route('admin.leads.index') }}">
                    <span>Lead Registry</span>
                    <small>Review qualification, assignment, and package stages</small>
                </a>
                <a href="{{ route('admin.testimonials.index') }}">
                    <span>Testimonials</span>
                    <small>Manage published proof and customer stories</small>
                </a>
                <a href="{{ route('admin.blog.index') }}">
                    <span>Content</span>
                    <small>Update blog posts and growth-facing content</small>
                </a>
                <a href="{{ route('listings') }}">
                    <span>Marketplace</span>
                    <small>Check the public property experience</small>
                </a>
            </nav>

            <article class="or-dashboard__profile-card">
                <div class="or-dashboard__profile-head">
                    <div class="or-dashboard__avatar">
                        <img src="{{ $workspaceAvatar }}" alt="{{ $workspaceUser?->name ?: 'Workspace user' }} profile image" loading="lazy">
                    </div>
                    <div class="or-dashboard__profile-copy">
                        <span class="eyebrow">{{ $isStaffView ? 'Staff Access' : 'Admin Access' }}</span>
                        <h2>{{ $workspaceUser?->name ?: 'OmniReferral Team' }}</h2>
                        <p>{{ $workspaceUser?->email ?: 'Operations access active' }}</p>
                    </div>
                </div>

                <div class="or-dashboard__chip-row">
                    <span>{{ $workspaceUser?->roleLabel() ?? 'Operations' }}</span>
                    <span>{{ number_format($stats['pending'] ?? 0) }} pending agents</span>
                    <span>{{ number_format($stats['pendingListings'] ?? 0) }} listing reviews</span>
                </div>

                <div class="or-dashboard__profile-grid">
                    @foreach($workspaceHighlights as $highlight)
                        <div>
                            <span>{{ $highlight['label'] }}</span>
                            <strong>{{ $highlight['value'] }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="or-dashboard__action-row">
                    <a href="{{ route('admin.leads.index') }}" class="button button--blue">Manage Leads</a>
                    <a href="{{ route('admin.blog.index') }}" class="button button--ghost-blue">Open Content</a>
                </div>
            </article>

            <article class="or-dashboard__mini-card">
                <span class="eyebrow">Revenue Pulse</span>
                <strong>Registry Active</strong>
                <p>Monitor high-intent leads as they flow through the system from website submissions and manual imports.</p>
                <div class="or-dashboard__mini-grid">
                    <div>
                        <span>Listed</span>
                        <strong>{{ number_format($stats['properties'] ?? 0) }}</strong>
                    </div>
                    <div>
                        <span>Partners</span>
                        <strong>{{ number_format($stats['realtors'] ?? 0) }}</strong>
                    </div>
                </div>
                <a href="{{ route('admin.testimonials.index') }}" class="button button--orange">Review Proof</a>
            </article>
        </aside>

        <main class="or-dashboard__main">
            <header class="or-dashboard__header">
                <div class="or-dashboard__header-copy">
                    <span class="eyebrow">Admin / Staff</span>
                    <h1>{{ $workspaceTitle }}</h1>
                    <p>{{ $workspaceCopy }}</p>
                    <div class="or-dashboard__header-chips">
                        <span>{{ number_format($summary['total']) }} matching leads</span>
                        <span>{{ number_format($summary['qualified']) }} qualified</span>
                        <span>{{ number_format($summary['rejected']) }} rejected</span>
                    </div>
                </div>

                <div class="or-dashboard__header-actions">
                    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Dashboard</a>
                    <a href="{{ route('admin.leads.export.csv', request()->query()) }}" class="button">Export Registry</a>
                </div>
            </header>

            <div class="or-dashboard__stat-row">
                <article class="or-dashboard__stat-card">
                    <span>Matching Leads</span>
                    <strong>{{ number_format($summary['total']) }}</strong>
                    <p>Records found in the current filtered registry view</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Qualified Leads</span>
                    <strong>{{ number_format($summary['qualified']) }}</strong>
                    <p>Leads verified as market-ready and approved</p>
                </article>
                <article class="or-dashboard__stat-card">
                    <span>Rejected Leads</span>
                    <strong>{{ number_format($summary['rejected']) }}</strong>
                    <p>Records marked as not interested or invalid</p>
                </article>
                <article class="or-dashboard__stat-card or-dashboard__stat-card--warm">
                    <span>Website Leads</span>
                    <strong>{{ number_format($summary['website']) }}</strong>
                    <p>Opportunities captured directly via OmniReferral forms</p>
                </article>
            </div>

            <div class="or-dashboard__content-grid">
                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Registry Filters</span>
                            <h2>Narrow lead opportunities</h2>
                            <p>Use search and category logic to find specific lead records fast.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.leads.index') }}" class="or-dashboard__filter-form">
                        <div class="or-dashboard__filter-grid">
                            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search name, phone, email, address..." />
                            <select name="intent">
                                <option value="">All intents</option>
                                @foreach($intents as $intent)
                                    <option value="{{ $intent }}" {{ $filters['intent'] === $intent ? 'selected' : '' }}>{{ ucfirst($intent) }}</option>
                                @endforeach
                            </select>
                            <select name="status">
                                <option value="">All statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_',' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <select name="source">
                                <option value="">All sources</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source }}" {{ $filters['source'] === $source ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($source) }}</option>
                                @endforeach
                            </select>
                            <select name="rep_name">
                                <option value="">All reps</option>
                                @foreach($repNames as $repName)
                                    <option value="{{ $repName }}" {{ $filters['rep_name'] === $repName ? 'selected' : '' }}>{{ $repName }}</option>
                                @endforeach
                            </select>
                            <div class="or-dashboard__filter-row">
                                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" />
                                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" />
                            </div>
                        </div>
                        <button type="submit" class="button button--blue">Refresh View</button>
                    </form>
                </section>

                <section class="or-dashboard__surface">
                    <div class="or-dashboard__surface-header">
                        <div>
                            <span class="eyebrow">Universal Import</span>
                            <h2>Import ops files directly</h2>
                            <p>Supported: CSV, XLSX, XLS, PDF, and JSON formats.</p>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.leads.import.csv') }}" enctype="multipart/form-data" class="or-dashboard__upload-form js-loading-form">
                        @csrf
                        <input type="file" name="lead_file" required />
                        <div class="or-dashboard__action-row">
                            <button type="submit" name="mode" value="import" class="button js-loading-btn">Import Leads</button>
                            <button type="submit" name="mode" value="preview" class="button button--ghost-blue js-loading-btn">Preview</button>
                        </div>
                    </form>

                    <div class="or-dashboard__tag-cloud" style="margin-top: 1.5rem;">
                        <span>Google Sheets Sync Active</span>
                        <form method="POST" action="{{ route('admin.leads.sync.google-sheets') }}" class="js-loading-form" style="display:inline;">
                            @csrf
                            <button type="submit" style="background:none; border:none; padding:0; color:var(--color-brand-orange); font-weight:700; cursor:pointer;" class="js-loading-btn">Sync Now</button>
                        </form>
                    </div>
                </section>
            </div>

            <section class="or-dashboard__surface or-dashboard__surface--wide">
                <div class="or-dashboard__surface-header">
                    <div>
                        <span class="eyebrow">Registry Data</span>
                        <h2>Lead operations records</h2>
                        <p>Track statuses, journey stages, and partner assignments across the entire system.</p>
                    </div>
                </div>

                <div class="or-dashboard__table-wrap">
                    <table class="or-dashboard__table">
                        <thead>
                            <tr>
                                <th>Lead Entity</th>
                                <th>Journey Stage</th>
                                <th>Market Point</th>
                                <th>Operations</th>
                                <th>Assignment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                                @php
                                    $statusTone = $lead->statusTone();
                                    $statusLabel = $lead->statusLabel();
                                    $priceSummary = $lead->budget
                                        ? 'Budget $' . number_format($lead->budget)
                                        : ($lead->asking_price ? 'Ask $' . number_format($lead->asking_price) : 'N/A');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $lead->name }}</strong>
                                            <span>{{ $lead->email ?: 'No email' }}</span>
                                            <small>{{ $lead->phone }} | {{ $lead->lead_number }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <span class="status-pill status-pill--{{ $statusTone }}">{{ $statusLabel }}</span>
                                            <small>{{ ucfirst($lead->intent) }} | {{ \Illuminate\Support\Str::headline($lead->source ?: 'Web') }}</small>
                                        </div>
                                        <form action="{{ route('admin.leads.status', $lead) }}" method="POST" class="js-loading-form" style="margin-top:0.5rem;">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" style="font-size:0.75rem; padding:0.25rem; border-radius:4px; border:1px solid #cbd0d8; background:#EAEBEF;">
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>{{ $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_',' ', $status)) }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $lead->property_address ?: 'No Address' }}</strong>
                                            <span>{{ $lead->zip_code }} | {{ $lead->state }}</span>
                                            <small>{{ $priceSummary }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <strong>{{ $lead->rep_name ?: 'No Rep' }}</strong>
                                            <span>Sent: {{ $lead->sent_to ?: 'N/A' }}</span>
                                            <small>{{ \Illuminate\Support\Str::limit($lead->reason_in_house ?: 'No reason', 40) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="or-dashboard__detail-stack">
                                            <span class="status-pill status-pill--{{ $lead->assigned_agent_id ? 'assigned' : 'new' }}">{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</span>
                                            <small>{{ $lead->assigned_at ? $lead->assigned_at->diffForHumans() : 'Awaiting routing' }}</small>
                                        </div>
                                        <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="or-dashboard__assign-form js-loading-form" style="margin-top:0.5rem; display:flex; gap:0.25rem;">
                                            @csrf
                                            <select name="agent_id" required style="font-size:0.75rem; padding:0.25rem; border-radius:4px; border:1px solid #cbd0d8; background:#EAEBEF; flex:1;">
                                                <option value="">Agent...</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ (int) $lead->assigned_agent_id === (int) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="js-loading-btn" style="background:none; border:none; color:var(--color-brand-blue); font-weight:700; cursor:pointer; font-size:0.75rem;">Set</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="or-dashboard__empty">
                                            <h3>No registry records found</h3>
                                            <p>Leads from imports and website captures will appear here as they are processed.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 2rem;">
                    {{ $leads->links() }}
                </div>
            </section>
        </main>

        <aside class="or-dashboard__rail">
            <article class="or-dashboard__summary-card">
                <span class="eyebrow">Registry Snapshot</span>
                <h3>Registry health</h3>
                <p>A quick view of leads processed and routing status across OmniReferral.</p>
                <strong class="or-dashboard__summary-total">{{ number_format($summary['total']) }}</strong>
                
                <div class="or-dashboard__summary-meta">
                    <div>
                        <span>Qualified</span>
                        <strong>{{ number_format($summary['qualified']) }}</strong>
                    </div>
                    <div>
                        <span>Rejected</span>
                        <strong>{{ number_format($summary['rejected']) }}</strong>
                    </div>
                    <div>
                        <span>Pending Review</span>
                        <strong>{{ number_format($stats['pendingListings']) }}</strong>
                    </div>
                    <div>
                        <span>Partners</span>
                        <strong>{{ number_format($stats['realtors']) }}</strong>
                    </div>
                </div>

                <div class="or-dashboard__summary-actions">
                    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Dashboard</a>
                    <a href="{{ route('admin.leads.export.csv', request()->query()) }}" class="button">Export CSV</a>
                </div>
            </article>

            <article class="or-dashboard__panel">
                <span class="eyebrow">Routing Focus</span>
                <h3>Action items</h3>
                <div class="or-dashboard__spotlight">
                    <article>
                        <span class="or-dashboard__spotlight-index">01</span>
                        <div>
                            <strong>Review unassigned records</strong>
                        </div>
                    </article>
                    <article>
                        <span class="or-dashboard__spotlight-index">02</span>
                        <div>
                            <strong>Qualify fresh website leads</strong>
                        </div>
                    </article>
                    <article>
                        <span class="or-dashboard__spotlight-index">03</span>
                        <div>
                            <strong>Sync latest Google Sheet rows</strong>
                        </div>
                    </article>
                </div>
            </article>
        </aside>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const btn = event.submitter || form.querySelector('.js-loading-btn');
            if (btn) {
                btn.disabled = true;
                btn.dataset.originalText = btn.textContent;
                btn.textContent = '...';
            }
        });
    });
});
</script>
@endpush
@endsection
