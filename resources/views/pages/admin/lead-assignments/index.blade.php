@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Lead Assignments')
@section('dashboard_description', 'View and manage all lead-to-agent assignments.')

@section('dashboard_actions')
    <a href="{{ route('admin.lead-assignments.create') }}" class="button">Assign Lead</a>
    @if(!$isStaffView)
        <form method="POST" action="{{ route('admin.lead-assignments.auto-assign') }}" style="display:inline" onsubmit="return confirm('Auto-assign unassigned leads to eligible agents?')">
            @csrf
            <button type="submit" class="button button--ghost-blue">Auto-Assign</button>
        </form>
    @endif
@endsection

@section('content')
<div class="workspace-stack">
    @if($isStaffView)
        <section class="workspace-card" style="border-left:4px solid #0b3668; padding: 1rem 1.5rem; background: #f0f7ff; color: #0b3668;">
            <p style="margin:0; font-weight:500;"><strong>Role View:</strong> Showing only assignments you created.</p>
        </section>
    @endif

    {{-- Totals --}}
    <section class="workspace-grid workspace-grid--4">
        <article class="workspace-card workspace-kpi" style="border-top: 4px solid #64748b;">
            <span>Total Leads</span>
            <strong>{{ number_format($totals['total_leads']) }}</strong>
            <span>In scope</span>
        </article>
        <article class="workspace-card workspace-kpi" data-trend="open" style="border-top: 4px solid #3b82f6;">
            <span>Assigned Leads</span>
            <strong>{{ number_format($totals['assigned_leads']) }}</strong>
            <span>Active assignments</span>
        </article>
        <article class="workspace-card workspace-kpi workspace-kpi--warm" data-trend="accepted" style="border-top: 4px solid #eab308;">
            <span>Unassigned Leads</span>
            <strong>{{ number_format($totals['unassigned_leads']) }}</strong>
            <span>Pending assignment</span>
        </article>
        <article class="workspace-card workspace-kpi" data-trend="rejected/removed" style="border-top: 4px solid #10b981;">
            <span>Closed Leads</span>
            <strong>{{ number_format($totals['closed_leads']) }}</strong>
            <span>Completed cycle</span>
        </article>
    </section>

    <section class="workspace-card">
        <span class="eyebrow" style="font-weight: 700; color: #0b3668; margin-bottom: 1rem; display: block;">Search & Filter Assignments</span>
        <form method="GET" action="{{ route('admin.lead-assignments.index') }}">
            <div class="workspace-form-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                <label class="workspace-field">
                    <span>Search lead</span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, or lead number">
                </label>
                <label class="workspace-field">
                    <span>Month</span>
                    <input type="month" name="month" value="{{ request('month') }}">
                </label>
                <label class="workspace-field">
                    <span>Status</span>
                    <select name="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Realtor</span>
                    <select name="agent_id">
                        <option value="">All realtors</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </label>
                @if(!$isStaffView)
                    <label class="workspace-field">
                        <span>Assigned By (Staff)</span>
                        <select name="staff_id">
                            <option value="">All staff</option>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                <label class="workspace-field">
                    <span>Package / Plan</span>
                    <select name="package_id">
                        <option value="">All packages</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}" {{ request('package_id') == $pkg->id ? 'selected' : '' }}>{{ $pkg->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>State</span>
                    <select name="state">
                        <option value="">All states</option>
                        @foreach($states as $st)
                            <option value="{{ $st }}" {{ request('state') == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>City</span>
                    <select name="city">
                        <option value="">All cities</option>
                        @foreach($cities as $ct)
                            <option value="{{ $ct }}" {{ request('city') == $ct ? 'selected' : '' }}>{{ $ct }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Assigned From</span>
                    <input type="date" name="date_from" value="{{ request('date_from') }}">
                </label>
                <label class="workspace-field">
                    <span>Assigned To</span>
                    <input type="date" name="date_to" value="{{ request('date_to') }}">
                </label>
                <div class="workspace-field" style="grid-column: 1 / -1; display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="submit" class="button">Filter</button>
                    <a href="{{ route('admin.lead-assignments.index') }}" class="button button--ghost-blue">Reset</a>
                </div>
            </div>
        </form>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════
         Bulk Actions Toolbar — slides in when ≥ 1 row is checked
    ═══════════════════════════════════════════════════════════════ --}}
    <section id="bulkToolbar" class="workspace-card" style="
        display: none;
        position: sticky;
        top: 0;
        z-index: 50;
        background: linear-gradient(135deg, #0b3668, #1e4a8a);
        color: #fff;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 8px 32px rgba(11, 54, 104, 0.25);
        animation: bulkSlideIn 0.25s ease-out;
    ">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <strong id="bulkCount" style="font-size: 1rem; white-space: nowrap;">0 selected</strong>
            <button type="button" id="bulkDeselectAll" style="
                background: none; border: 1px solid rgba(255,255,255,0.35); color: #fff;
                padding: 0.3rem 0.75rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.8rem;
            ">Deselect All</button>

            <span style="flex: 1;"></span>

            {{-- Bulk Status Update --}}
            <form method="POST" action="{{ route('admin.lead-assignments.bulk-status') }}" id="bulkStatusForm" style="display: flex; gap: 0.4rem; align-items: center;">
                @csrf
                <div id="bulkStatusIds"></div>
                <select name="assignment_status" required style="
                    padding: 0.35rem 0.6rem; border-radius: 0.375rem; border: 1px solid rgba(255,255,255,0.3);
                    background: rgba(255,255,255,0.15); color: #fff; font-size: 0.8rem; min-width: 130px;
                ">
                    <option value="" style="color:#333;">Set status…</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" style="color:#333;">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" style="
                    background: #38bdf8; color: #0c2d52; border: none; padding: 0.35rem 0.75rem;
                    border-radius: 0.375rem; font-weight: 600; cursor: pointer; font-size: 0.8rem; white-space: nowrap;
                ">Update Status</button>
            </form>

            {{-- Bulk Reassign --}}
            <form method="POST" action="{{ route('admin.lead-assignments.bulk-reassign') }}" id="bulkReassignForm" style="display: flex; gap: 0.4rem; align-items: center;">
                @csrf
                <div id="bulkReassignIds"></div>
                <select name="agent_id" required style="
                    padding: 0.35rem 0.6rem; border-radius: 0.375rem; border: 1px solid rgba(255,255,255,0.3);
                    background: rgba(255,255,255,0.15); color: #fff; font-size: 0.8rem; min-width: 130px;
                ">
                    <option value="" style="color:#333;">Reassign to…</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" style="color:#333;">{{ $agent->name }}</option>
                    @endforeach
                </select>
                <button type="submit" style="
                    background: #a5f3fc; color: #0c2d52; border: none; padding: 0.35rem 0.75rem;
                    border-radius: 0.375rem; font-weight: 600; cursor: pointer; font-size: 0.8rem; white-space: nowrap;
                ">Reassign</button>
            </form>

            {{-- Bulk Remove --}}
            <form method="POST" action="{{ route('admin.lead-assignments.bulk-remove') }}" id="bulkRemoveForm"
                  onsubmit="return confirm('Remove selected assignments and return leads to the unassigned pool?');"
                  style="display: inline;">
                @csrf
                <div id="bulkRemoveIds"></div>
                <button type="submit" style="
                    background: rgba(239, 68, 68, 0.85); color: #fff; border: none; padding: 0.35rem 0.75rem;
                    border-radius: 0.375rem; font-weight: 600; cursor: pointer; font-size: 0.8rem; white-space: nowrap;
                ">Remove Selected</button>
            </form>
        </div>
    </section>

    <section class="workspace-card">
        <div class="table-scroll">
            <table class="table" id="assignmentsTable" style="width:100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1; text-align: left;">
                        <th style="padding: 10px; width: 40px;">
                            <label style="cursor:pointer; display:flex; align-items:center; gap:0.3rem;" title="Select all on this page">
                                <input type="checkbox" id="selectAllCheckbox" style="width:16px; height:16px; accent-color: #0b3668; cursor:pointer;">
                            </label>
                        </th>
                        <th style="padding: 10px;">Lead ID</th>
                        <th style="padding: 10px;">Lead Name</th>
                        <th style="padding: 10px;">Buyer/Seller</th>
                        <th style="padding: 10px;">Assignment Status</th>
                        <th style="padding: 10px;">Lead Source</th>
                        <th style="padding: 10px;">Assigned Realtor</th>
                        <th style="padding: 10px;">Assigned By</th>
                        <th style="padding: 10px;">Assigned Date</th>
                        <th style="padding: 10px;">Current Stage</th>
                        <th style="padding: 10px;">Priority</th>
                        <th style="padding: 10px;">Package/Plan</th>
                        <th style="padding: 10px;">City</th>
                        <th style="padding: 10px;">State</th>
                        <th style="padding: 10px;">ZIP Code</th>
                        <th style="padding: 10px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr style="border-bottom: 1px solid #e2e8f0; vertical-align: middle;" data-assignment-id="{{ $assignment->id }}">
                            <td style="padding: 12px 10px;">
                                <input type="checkbox" class="bulk-checkbox" value="{{ $assignment->id }}"
                                       style="width:16px; height:16px; accent-color: #0b3668; cursor:pointer;">
                            </td>
                            <td style="padding: 12px 10px;">
                                <span style="font-family: monospace; font-weight:600; color: #475569;">#{{ $assignment->lead->id ?? $assignment->lead_id }}</span>
                                @if($assignment->lead?->lead_number)
                                    <br><span style="font-size:0.75rem; color:#64748b;">{{ $assignment->lead->lead_number }}</span>
                                @endif
                            </td>
                            <td style="padding: 12px 10px;">
                                <a href="{{ route('admin.leads.index', ['search' => $assignment->lead->email ?? $assignment->lead->name]) }}" class="link" style="font-weight: 500;">
                                    {{ $assignment->lead->name ?? 'Lead #'.$assignment->lead_id }}
                                </a>
                                <br><small class="muted" style="color: #64748b;">{{ $assignment->lead->email }}</small>
                            </td>
                            <td style="padding: 12px 10px;">
                                <span class="badge" style="background:#f1f5f9; color:#475569;">{{ ucfirst($assignment->lead->intent ?? 'N/A') }}</span>
                            </td>
                            <td style="padding: 12px 10px;">
                                @if($assignment->assignment_status === 'accepted')
                                    <span class="badge" style="background: #dcfce7; color: #15803d; font-weight: 600;">Accepted (Good)</span>
                                @elseif($assignment->assignment_status === 'rejected')
                                    <span class="badge" style="background: #fee2e2; color: #b91c1c; font-weight: 600;">Rejected (Returned)</span>
                                @elseif($assignment->assignment_status === 'removed')
                                    <span class="badge" style="background: #f1f5f9; color: #475569;">Removed</span>
                                @elseif($assignment->assignment_status === 'reassigned')
                                    <span class="badge" style="background: #dbeafe; color: #1d4ed8;">Reassigned</span>
                                @elseif($assignment->assignment_status === 'closed')
                                    <span class="badge" style="background: #e2e8f0; color: #1e293b;">Closed</span>
                                @else
                                    <span class="badge" style="background: #fef9c3; color: #a16207;">Assigned (Pending)</span>
                                @endif
                            </td>
                            <td style="padding: 12px 10px;">
                                {{ ucfirst($assignment->lead->source ?? 'N/A') }}
                            </td>
                            <td style="padding: 12px 10px; font-weight: 500;">
                                {{ $assignment->assignedTo?->name ?? 'N/A' }}
                            </td>
                            <td style="padding: 12px 10px;">
                                {{ $assignment->assignedBy?->name ?? 'System' }}
                            </td>
                            <td style="padding: 12px 10px; color:#475569; font-size:0.8rem;">
                                {{ $assignment->assigned_at?->format('M j, Y g:i A') ?? ($assignment->sent_at?->format('M j, Y g:i A') ?? $assignment->created_at->format('M j, Y g:i A')) }}
                            </td>
                            <td style="padding: 12px 10px;">
                                <span class="badge" style="background:#e8f4fd; color:#0b3668;">
                                    {{ $assignment->lead ? $assignment->lead->statusLabel() : 'N/A' }}
                                </span>
                            </td>
                            <td style="padding: 12px 10px;">
                                @if($assignment->lead?->is_priority)
                                    <span class="badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fcd34d;">Priority</span>
                                @else
                                    <span style="color:#94a3b8;">Normal</span>
                                @endif
                            </td>
                            <td style="padding: 12px 10px;">
                                {{ $assignment->package?->name ?? 'N/A' }}
                            </td>
                            <td style="padding: 12px 10px;">
                                {{ $assignment->lead->city ?? '—' }}
                            </td>
                            <td style="padding: 12px 10px;">
                                {{ $assignment->lead->state ?? '—' }}
                            </td>
                            <td style="padding: 12px 10px;">
                                {{ $assignment->lead->zip_code ?? '—' }}
                            </td>
                            <td style="padding: 12px 10px; text-align: right;">
                                <div style="display:flex; flex-direction:column; gap:0.3rem; align-items:flex-end;">
                                    <a href="{{ route('admin.lead-assignments.show', $assignment) }}" class="link" style="font-weight:600;">View History</a>
                                    @if(! in_array($assignment->assignment_status, ['reassigned', 'removed', 'closed']))
                                        <form method="POST" action="{{ route('admin.lead-assignments.reassign', $assignment) }}" style="display:inline-flex; gap:0.2rem; align-items:center;">
                                            @csrf
                                            <select name="agent_id" required style="max-width:8rem; padding:0.25rem; font-size:0.75rem; border:1px solid #cbd5e1; border-radius:0.375rem;">
                                                <option value="">Reassign…</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ (int) $agent->id === (int) $assignment->assigned_to_user_id ? 'disabled' : '' }}>{{ $agent->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="button button--ghost-blue" style="font-size:0.7rem; padding:0.25rem 0.5rem; line-height: 1.2;">Reassign</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.lead-assignments.remove', $assignment) }}"
                                              onsubmit="return confirm('Remove this assignment and return the lead to the unassigned pool?');">
                                            @csrf
                                            <button type="submit" class="button button--ghost-blue" style="font-size:0.7rem; padding:0.25rem 0.5rem; color:#b91c1c; line-height: 1.2;">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center muted" style="padding: 20px; color:#64748b;">No assignments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper" style="margin-top: 1.5rem;">
            {{ $assignments->links() }}
        </div>
    </section>
</div>

<style>
    @keyframes bulkSlideIn {
        from { opacity: 0; transform: translateY(-12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    tr[data-assignment-id] {
        transition: background-color 0.15s ease;
    }
    tr[data-assignment-id].bulk-selected {
        background-color: #eff6ff !important;
    }
    tr[data-assignment-id].bulk-selected td:first-child {
        position: relative;
    }
    tr[data-assignment-id].bulk-selected td:first-child::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #0b3668;
        border-radius: 0 3px 3px 0;
    }

    #bulkToolbar select option {
        background: #fff;
        color: #1e293b;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllCheckbox');
    const toolbar   = document.getElementById('bulkToolbar');
    const countEl   = document.getElementById('bulkCount');
    const deselectBtn = document.getElementById('bulkDeselectAll');
    const checkboxes = () => document.querySelectorAll('.bulk-checkbox');

    function getCheckedIds() {
        return Array.from(checkboxes()).filter(cb => cb.checked).map(cb => cb.value);
    }

    function syncToolbar() {
        const ids = getCheckedIds();
        const count = ids.length;

        toolbar.style.display = count > 0 ? 'block' : 'none';
        countEl.textContent = count + ' selected';

        // Sync select-all visual state
        const all = checkboxes();
        const allChecked = all.length > 0 && Array.from(all).every(cb => cb.checked);
        const someChecked = count > 0 && !allChecked;
        selectAll.checked = allChecked;
        selectAll.indeterminate = someChecked;

        // Populate hidden inputs for each bulk form
        ['bulkStatusIds', 'bulkReassignIds', 'bulkRemoveIds'].forEach(containerId => {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'assignment_ids[]';
                input.value = id;
                container.appendChild(input);
            });
        });

        // Highlight selected rows
        document.querySelectorAll('tr[data-assignment-id]').forEach(row => {
            const cb = row.querySelector('.bulk-checkbox');
            row.classList.toggle('bulk-selected', cb && cb.checked);
        });
    }

    // Individual checkbox change
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('bulk-checkbox') || e.target === selectAll) {
            if (e.target === selectAll) {
                checkboxes().forEach(cb => { cb.checked = selectAll.checked; });
            }
            syncToolbar();
        }
    });

    // Deselect all button
    deselectBtn.addEventListener('click', function () {
        selectAll.checked = false;
        checkboxes().forEach(cb => { cb.checked = false; });
        syncToolbar();
    });

    // Shift+click range selection
    let lastChecked = null;
    document.addEventListener('click', function (e) {
        if (!e.target.classList.contains('bulk-checkbox')) return;

        if (e.shiftKey && lastChecked) {
            const boxes = Array.from(checkboxes());
            const start = boxes.indexOf(lastChecked);
            const end = boxes.indexOf(e.target);
            const [from, to] = start < end ? [start, end] : [end, start];
            for (let i = from; i <= to; i++) {
                boxes[i].checked = e.target.checked;
            }
            syncToolbar();
        }

        lastChecked = e.target;
    });
});
</script>
@endsection
