@extends('layouts.dashboard')

@section('dashboard_eyebrow', $isStaffView ? 'Staff Workspace' : 'Admin Workspace')
@section('dashboard_title', 'Lead Registry')
@section('dashboard_description', 'Filter, import, add, assign, and auto-sync lead records from one fully responsive operations page.')

@section('dashboard_actions')
    <a href="{{ route('admin.dashboard') }}" class="button button--ghost-blue">Overview</a>
    <button type="button" class="button button--ghost-blue" onclick="openAddLeadModal()">+ Add New Lead</button>
    <a href="{{ route('admin.leads.export.csv', request()->query()) }}" class="button">Export CSV</a>
@endsection

@section('content')
<div class="workspace-stack">
    <!-- Live Auto-Sync Status Bar -->
    <div class="workspace-card" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98)); border: 1px solid rgba(255,255,255,0.08); padding: 1rem 1.25rem; border-radius: 12px; color: #fff;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;" id="syncIndicator"></span>
            <div>
                <strong style="font-size: 0.95rem; display: block; color: #f8fafc;">Live Google Sheet Auto-Sync</strong>
                <span style="font-size: 0.8rem; color: #94a3b8;" id="syncStatusBadge">Connected · Auto-syncing every 30s in background</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <label style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #cbd5e1; cursor: pointer;">
                <input type="checkbox" id="toggleAutoSync" checked onchange="toggleAutoSyncState(this.checked)"> Auto-Sync ON
            </label>
            <button type="button" class="button button--ghost-blue" id="btnSyncSheetNow" onclick="triggerSheetSync(true)" style="padding: 0.45rem 0.9rem; font-size: 0.85rem;">
                🔄 Sync Sheet Now
            </button>
        </div>
    </div>

    <!-- Toast Notification Banner -->
    <div id="leadSyncToast" style="display: none; padding: 0.85rem 1.2rem; border-radius: 8px; font-size: 0.9rem; font-weight: 500; transition: all 0.3s ease;"></div>

    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi">
            <span>Matching Leads</span>
            <strong id="kpiTotal">{{ number_format($summary['total']) }}</strong>
            <span>Current filtered records</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Qualified</span>
            <strong id="kpiQualified">{{ number_format($summary['qualified']) }}</strong>
            <span>Conversion-ready leads</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Rejected</span>
            <strong id="kpiRejected">{{ number_format($summary['rejected']) }}</strong>
            <span>Not interested or invalid</span>
        </article>
        <article class="workspace-card workspace-kpi">
            <span>Website Source</span>
            <strong id="kpiWebsite">{{ number_format($summary['website']) }}</strong>
            <span>Captured directly on-site</span>
        </article>
    </section>

    <section class="workspace-grid workspace-grid--2">
        <article class="workspace-card">
            <span class="eyebrow">Filters</span>
            <h2>Refine Lead Results</h2>
            <form method="GET" action="{{ route('admin.leads.index') }}">
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Search</span>
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Name, email, phone, address...">
                    </label>
                    <label class="workspace-field">
                        <span>Intent</span>
                        <select name="intent">
                            <option value="">All intents</option>
                            @foreach($intents as $intent)
                                <option value="{{ $intent }}" {{ $filters['intent'] === $intent ? 'selected' : '' }}>{{ ucfirst($intent) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Status</span>
                        <select name="status">
                            <option value="">All statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>
                                    {{ $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Source</span>
                        <select name="source">
                            <option value="">All sources</option>
                            @foreach($sources as $source)
                                <option value="{{ $source }}" {{ $filters['source'] === $source ? 'selected' : '' }}>{{ \Illuminate\Support\Str::headline($source) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Rep Name</span>
                        <select name="rep_name">
                            <option value="">All reps</option>
                            @foreach($repNames as $repName)
                                <option value="{{ $repName }}" {{ $filters['rep_name'] === $repName ? 'selected' : '' }}>{{ $repName }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="workspace-field">
                        <span>Date From</span>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
                    </label>
                    <label class="workspace-field">
                        <span>Date To</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
                    </label>
                </div>
                <div class="workspace-actions" style="margin-top: 0.8rem;">
                    <button type="submit" class="button">Apply Filters</button>
                </div>
            </form>
        </article>

        <article class="workspace-card">
            <span class="eyebrow">Import & Actions</span>
            <h2>Lead File Operations & Add</h2>
            <form method="POST" action="{{ route('admin.leads.import.csv') }}" enctype="multipart/form-data" class="js-loading-form">
                @csrf
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Upload File (CSV, XLSX, PDF, DOCX)</span>
                        <input type="file" name="lead_file" required>
                    </label>
                </div>
                <div class="workspace-actions" style="margin-top: 0.8rem;">
                    <button type="submit" name="mode" value="import" class="button js-loading-btn">Import Leads</button>
                    <button type="submit" name="mode" value="preview" class="button button--ghost-blue js-loading-btn">Preview</button>
                </div>
            </form>
            <div class="workspace-actions" style="margin-top: 0.85rem; display: flex; gap: 0.5rem;">
                <button type="button" class="button button--ghost-blue" onclick="openAddLeadModal()" style="flex: 1;">+ Add Lead Manually</button>
                <form method="POST" action="{{ route('admin.leads.sync.google-sheets') }}" class="js-loading-form" style="flex: 1;">
                    @csrf
                    <button type="submit" class="button button--ghost-blue js-loading-btn" style="width: 100%;">Sync Sheet (Full Reload)</button>
                </form>
            </div>
        </article>
    </section>

    <section class="workspace-card">
        <div class="workspace-table-wrap">
            <table class="workspace-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Stage</th>
                        <th>Market</th>
                        <th>Ops</th>
                        <th>Assignment</th>
                    </tr>
                </thead>
                <tbody>
                    @include('pages.admin.leads.partials.table_rows')
                </tbody>
            </table>
        </div>

        <div class="workspace-pagination">
            {{ $leads->links() }}
        </div>
    </section>
</div>

<!-- Modal: Add Lead Form (Admin & Staff Access) -->
<div id="addLeadModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem; overflow-y: auto;">
    <div style="background: #0f172a; border: 1px solid rgba(255,255,255,0.12); border-radius: 16px; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.5); padding: 1.75rem; color: #f8fafc;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1rem; margin-bottom: 1.25rem;">
            <div>
                <span class="eyebrow" style="color: #60a5fa;">Manual Lead Entry</span>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin: 0.2rem 0 0; color: #fff;">Add New Lead</h3>
            </div>
            <button type="button" onclick="closeAddLeadModal()" style="background: transparent; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; padding: 0.2rem 0.5rem;">&times;</button>
        </div>

        <form id="addLeadForm" onsubmit="submitAddLeadForm(event)">
            @csrf
            <div class="workspace-form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                <label class="workspace-field">
                    <span>Lead Name <strong style="color: #ef4444;">*</strong></span>
                    <input type="text" name="name" required placeholder="John Doe">
                </label>

                <label class="workspace-field">
                    <span>Email Address <span style="color: #94a3b8; font-size: 0.75rem;">(Optional - leaves blank if no email)</span></span>
                    <input type="email" name="email" placeholder="john@example.com (Optional)">
                </label>

                <label class="workspace-field">
                    <span>Phone Number</span>
                    <input type="text" name="phone" placeholder="(555) 123-4567">
                </label>

                <label class="workspace-field">
                    <span>Intent <strong style="color: #ef4444;">*</strong></span>
                    <select name="intent" required>
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                        <option value="investor">Investor</option>
                        <option value="other">Other</option>
                    </select>
                </label>

                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="in_progress">In Progress</option>
                        <option value="qualified">Qualified</option>
                        <option value="assigned">Assigned</option>
                        <option value="closed">Closed</option>
                        <option value="not_interested">Rejected / Not Interested</option>
                    </select>
                </label>

                <label class="workspace-field">
                    <span>Property Address / Desired Area</span>
                    <input type="text" name="property_address" placeholder="123 Main St, Miami FL">
                </label>

                <label class="workspace-field">
                    <span>Beds & Baths</span>
                    <input type="text" name="beds_baths" placeholder="3 Beds / 2 Baths">
                </label>

                <label class="workspace-field">
                    <span>Budget ($)</span>
                    <input type="number" name="budget" step="1000" placeholder="450000">
                </label>

                <label class="workspace-field">
                    <span>Asking Price ($)</span>
                    <input type="number" name="asking_price" step="1000" placeholder="500000">
                </label>

                <label class="workspace-field">
                    <span>Working with Realtor already?</span>
                    <select name="working_with_realtor">
                        <option value="">Select option...</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </label>

                <label class="workspace-field">
                    <span>Timeline</span>
                    <input type="text" name="timeline" placeholder="30-60 Days / Immediate">
                </label>

                <label class="workspace-field">
                    <span>DNC Disclaimer Clear YES?</span>
                    <input type="text" name="dnc_disclaimer" placeholder="YES / Agreed">
                </label>

                <label class="workspace-field">
                    <span>Rep Name</span>
                    <input type="text" name="rep_name" placeholder="Sales Rep Name">
                </label>

                <label class="workspace-field">
                    <span>State of Buying/Selling</span>
                    <input type="text" name="state" placeholder="FL / NY / CA">
                </label>

                <label class="workspace-field">
                    <span>Sent To / Whom to send</span>
                    <input type="text" name="sent_to" placeholder="Broker / Partner name">
                </label>

                <label class="workspace-field">
                    <span>Assignment</span>
                    <input type="text" name="assignment" placeholder="Internal desk / team">
                </label>

                <label class="workspace-field">
                    <span>Reason: In-House</span>
                    <input type="text" name="reason_in_house" placeholder="Direct referral / In-house overflow">
                </label>

                <label class="workspace-field">
                    <span>Response from Realtor</span>
                    <input type="text" name="realtor_response" placeholder="Feedback or agent notes">
                </label>

                <label class="workspace-field">
                    <span>Assign Agent</span>
                    <select name="assigned_agent_id">
                        <option value="">Unassigned</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="workspace-field" style="grid-column: 1 / -1;">
                    <span>Notes</span>
                    <textarea name="notes" rows="3" placeholder="Additional notes..."></textarea>
                </label>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1rem;">
                <button type="button" class="button button--ghost-blue" onclick="closeAddLeadModal()">Cancel</button>
                <button type="submit" class="button" id="btnAddLeadSubmit">Create Lead</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let autoSyncTimer = null;
let autoSyncEnabled = true;

function showToast(message, type = 'success') {
    const toast = document.getElementById('leadSyncToast');
    if (!toast) return;
    toast.style.display = 'block';
    toast.style.background = type === 'success' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)';
    toast.style.border = type === 'success' ? '1px solid rgba(16, 185, 129, 0.4)' : '1px solid rgba(239, 68, 68, 0.4)';
    toast.style.color = type === 'success' ? '#34d399' : '#f87171';
    toast.innerText = message;

    setTimeout(() => {
        toast.style.display = 'none';
    }, 6000);
}

function toggleAutoSyncState(enabled) {
    autoSyncEnabled = enabled;
    const badge = document.getElementById('syncStatusBadge');
    const indicator = document.getElementById('syncIndicator');

    if (enabled) {
        if (badge) badge.innerText = 'Connected · Auto-syncing every 30s in background';
        if (indicator) indicator.style.background = '#10b981';
    } else {
        if (badge) badge.innerText = 'Auto-sync paused';
        if (indicator) indicator.style.background = '#f59e0b';
    }
}

function triggerSheetSync(showNotification = true) {
    const syncBtn = document.getElementById('btnSyncSheetNow');
    const statusBadge = document.getElementById('syncStatusBadge');
    if (syncBtn) syncBtn.disabled = true;
    if (statusBadge) statusBadge.innerText = 'Syncing with Google Sheets...';

    fetch('{{ route("admin.leads.sync.google-sheets") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (syncBtn) syncBtn.disabled = false;
        if (data.success) {
            const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            if (statusBadge) statusBadge.innerText = `Synced at ${timeStr} · Added: ${data.created || 0}, Skipped: ${data.skipped || 0}`;

            if (data.created > 0 || showNotification) {
                if (showNotification) {
                    showToast(data.message || `Google Sheets synced successfully. Total leads: ${data.total_leads || ''}`);
                }
                fetchLiveData();
            }
        } else {
            if (statusBadge) statusBadge.innerText = 'Sync error: ' + (data.message || 'Failed');
            if (showNotification) showToast(data.message || 'Sync failed', 'error');
        }
    })
    .catch(err => {
        if (syncBtn) syncBtn.disabled = false;
        if (statusBadge) statusBadge.innerText = 'Sync network error';
        if (showNotification) showToast('Network error while syncing Google Sheet', 'error');
    });
}

function fetchLiveData() {
    const currentUrl = new URL(window.location.href);
    fetch('{{ route("admin.leads.live-data") }}' + currentUrl.search, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.html) {
            const tbody = document.querySelector('.workspace-table tbody');
            if (tbody) tbody.innerHTML = data.html;
            if (data.summary) {
                const totalEl = document.getElementById('kpiTotal');
                const qualEl = document.getElementById('kpiQualified');
                const rejEl = document.getElementById('kpiRejected');
                const webEl = document.getElementById('kpiWebsite');

                if (totalEl) totalEl.innerText = Number(data.summary.total).toLocaleString();
                if (qualEl) qualEl.innerText = Number(data.summary.qualified).toLocaleString();
                if (rejEl) rejEl.innerText = Number(data.summary.rejected).toLocaleString();
                if (webEl) webEl.innerText = Number(data.summary.website).toLocaleString();
            }
        }
    })
    .catch(err => console.error('Failed to fetch live data:', err));
}

function openAddLeadModal() {
    const modal = document.getElementById('addLeadModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddLeadModal() {
    const modal = document.getElementById('addLeadModal');
    if (modal) modal.style.display = 'none';
}

function submitAddLeadForm(event) {
    event.preventDefault();
    const form = document.getElementById('addLeadForm');
    const submitBtn = document.getElementById('btnAddLeadSubmit');
    if (!form) return;

    if (submitBtn) submitBtn.disabled = true;
    const formData = new FormData(form);

    fetch('{{ route("admin.leads.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (submitBtn) submitBtn.disabled = false;
        if (data.success) {
            closeAddLeadModal();
            form.reset();
            showToast(data.message || 'Lead created successfully!');
            fetchLiveData();
        } else {
            showToast(data.message || 'Validation error while adding lead', 'error');
        }
    })
    .catch(err => {
        if (submitBtn) submitBtn.disabled = false;
        showToast('Error submitting form. Please check your inputs.', 'error');
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-loading-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const button = event.submitter || form.querySelector('.js-loading-btn');
            if (button) {
                button.disabled = true;
            }
        });
    });

    // Start 30-second live auto-sync polling
    autoSyncTimer = setInterval(() => {
        if (autoSyncEnabled) {
            triggerSheetSync(false);
        }
    }, 30000);
});
</script>
@endpush
@endsection
