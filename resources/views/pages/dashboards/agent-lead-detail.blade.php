@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Agent Workspace')
@section('dashboard_title', $lead->name)
@section('dashboard_description', 'Lead ID: ' . ($lead->lead_number ?: '#' . $lead->id) . ' · Created ' . $lead->created_at->format('M j, Y'))

@section('dashboard_actions')
    <a href="{{ route('agent.leads.index') }}" class="button button--ghost-blue">&larr; Back to Lead Queue</a>
@endsection

@push('styles')
<style>
.lead-header-banner {
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.lead-header-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.lead-avatar {
    width: 3.2rem;
    height: 3.2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #0b3668, #1d5fa0);
    color: #fff;
    font-size: 1.2rem;
    font-weight: 700;
    display: grid;
    place-items: center;
    box-shadow: 0 4px 12px rgba(11,54,104,0.2);
}
.lead-intent-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
}
.lead-intent-badge--buyer  { background: rgba(14,165,233,0.12); color: #0369a1; }
.lead-intent-badge--seller { background: rgba(255,107,0,0.12); color: #c2410c; }
.lead-intent-badge--investor { background: rgba(109,93,252,0.12); color: #5145cd; }
.lead-intent-badge--other { background: rgba(100,116,139,0.12); color: #475569; }

.quick-actions-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.9rem;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid var(--dash-shell-border);
    background: #fff;
    color: var(--dash-shell-text);
    transition: all 0.2s ease;
}
.action-btn:hover {
    border-color: #0b3668;
    background: rgba(11,54,104,0.05);
    color: #0b3668;
}
.action-btn--primary {
    background: #0b3668;
    color: #fff;
    border-color: #0b3668;
}
.action-btn--primary:hover {
    background: #1d5fa0;
    color: #fff;
}
.action-btn--success {
    background: #16a34a;
    color: #fff;
    border-color: #16a34a;
}
.action-btn--success:hover {
    background: #15803d;
    color: #fff;
}
.action-btn svg {
    width: 1rem;
    height: 1rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
}
.details-box {
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    border-radius: 12px;
    padding: 0.9rem 1.1rem;
}
.details-box__label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--dash-shell-muted);
    margin-bottom: 0.25rem;
}
.details-box__value {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--dash-shell-text);
    word-break: break-word;
}

.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin-top: 1rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0.5rem;
    bottom: 0.5rem;
    width: 2px;
    background: var(--dash-shell-border);
}
.timeline-item {
    position: relative;
    margin-bottom: 1.25rem;
}
.timeline-dot {
    position: absolute;
    left: -1.5rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #0b3668;
}
.timeline-dot--status { border-color: #ff6b00; background: #fff; }
.timeline-dot--call   { border-color: #16a34a; background: #16a34a; }
.timeline-dot--email  { border-color: #0369a1; background: #0369a1; }
.timeline-dot--sms    { border-color: #5145cd; background: #5145cd; }
.timeline-dot--edit   { border-color: #64748b; background: #64748b; }
.timeline-content {
    background: var(--dash-shell-panel-soft);
    border: 1px solid var(--dash-shell-border);
    border-radius: 10px;
    padding: 0.75rem 1rem;
}
.timeline-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: var(--dash-shell-muted);
    margin-bottom: 0.3rem;
}

.tag-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: #e2e8f0;
    color: #334155;
    font-size: 0.78rem;
    font-weight: 600;
}

/* Modal styles */
.modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-backdrop.is-active {
    display: flex;
}
.modal-box {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 620px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 1.5rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--dash-shell-border);
}
.modal-header h3 {
    margin: 0;
    font-size: 1.15rem;
    color: var(--dash-shell-text);
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.4rem;
    cursor: pointer;
    color: var(--dash-shell-muted);
}
</style>
@endpush

@section('content')
<div class="workspace-stack">

    {{-- Top Banner / Header --}}
    <section class="lead-header-banner">
        <div class="lead-header-info">
            <div class="lead-avatar">
                {{ strtoupper(substr($lead->name, 0, 1)) }}
            </div>
            <div>
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    <h1 style="font-size:1.4rem; font-weight:800; margin:0; color:var(--dash-shell-text);">{{ $lead->name }}</h1>
                    <span class="lead-intent-badge lead-intent-badge--{{ strtolower($lead->intent ?? 'buyer') }}">
                        {{ ucfirst($lead->intent ?? 'Buyer') }}
                    </span>
                    <span class="status-pill status-pill--{{ $lead->statusTone() }}">
                        {{ $lead->statusLabel() }}
                    </span>
                    @if($lead->is_priority)
                        <span class="status-pill status-pill--warm" style="background:#fff7ed; color:#c2410c; border:1px solid #ffedd5;">Priority</span>
                    @endif
                </div>
                <div style="font-size:0.83rem; color:var(--dash-shell-muted); margin-top:0.3rem;">
                    Lead ID: <strong>{{ $lead->lead_number ?: '#'.$lead->id }}</strong> · Assigned {{ $lead->assigned_at?->format('M j, Y') ?? $lead->created_at->format('M j, Y') }}
                </div>
            </div>
        </div>

        {{-- Header Quick Actions --}}
        <div class="quick-actions-bar">
            @if($lead->phone)
                <a href="tel:{{ $lead->phone }}" onclick="logQuickAction('call')" class="action-btn action-btn--success" title="Call Lead">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Call
                </a>
                <a href="sms:{{ $lead->phone }}" onclick="logQuickAction('sms')" class="action-btn" title="Send SMS">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    SMS
                </a>
            @endif
            @if($lead->email)
                <a href="mailto:{{ $lead->email }}" onclick="logQuickAction('email')" class="action-btn" title="Send Email">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    Email
                </a>
            @endif
            <button type="button" onclick="openModal('editLeadModal')" class="action-btn action-btn--primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Lead
            </button>
        </div>
    </section>

    {{-- Quick Control & Status Row --}}
    <section class="workspace-grid workspace-grid--3">

        {{-- Status Updater Card --}}
        <article class="workspace-card">
            <span class="eyebrow">Status Control</span>
            <h2 style="font-size:1.05rem; margin-bottom:0.6rem;">Change Lead Status</h2>
            <form action="{{ route('agent.leads.status', $lead) }}" method="POST" class="lead-status-form">
                @csrf
                <div class="workspace-form-grid" style="grid-template-columns:1fr auto; gap:0.5rem;">
                    <select name="status" class="workspace-field" style="margin:0;">
                        @foreach(['new' => 'New', 'contacted' => 'Contacted', 'in_progress' => 'In Progress', 'qualified' => 'Qualified', 'closed' => 'Closed', 'not_interested' => 'Not Interested'] as $val => $lbl)
                            <option value="{{ $val }}" {{ $lead->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="button button--ghost-blue" style="padding:0.4rem 0.8rem;">Update</button>
                </div>
            </form>
        </article>

        {{-- Add Note Action Card --}}
        <article class="workspace-card">
            <span class="eyebrow">Quick Action</span>
            <h2 style="font-size:1.05rem; margin-bottom:0.6rem;">Add Activity Note</h2>
            <form action="{{ route('agent.leads.activity', $lead) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="note">
                <div style="display:flex; gap:0.4rem;">
                    <input type="text" name="content" placeholder="Type quick update or note..." required style="flex:1; border:1px solid var(--dash-shell-border); border-radius:8px; padding:0.4rem 0.6rem; font-size:0.83rem;">
                    <button type="submit" class="button button--ghost-blue" style="padding:0.4rem 0.8rem;">Post</button>
                </div>
            </form>
        </article>

        {{-- Schedule Follow-up Card --}}
        <article class="workspace-card">
            <span class="eyebrow">Schedule</span>
            <h2 style="font-size:1.05rem; margin-bottom:0.6rem;">Set Follow-up Reminder</h2>
            <form action="{{ route('agent.leads.activity', $lead) }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="reminder">
                <input type="hidden" name="value" value="Follow-up Scheduled">
                <div style="display:grid; gap:0.4rem;">
                    <input type="datetime-local" name="due_at" required style="width:100%; border:1px solid var(--dash-shell-border); border-radius:8px; padding:0.35rem 0.5rem; font-size:0.8rem;">
                    <div style="display:flex; gap:0.4rem;">
                        <input type="text" name="content" placeholder="Reminder task details..." style="flex:1; border:1px solid var(--dash-shell-border); border-radius:8px; padding:0.35rem 0.5rem; font-size:0.8rem;">
                        <button type="submit" class="button" style="padding:0.35rem 0.75rem; font-size:0.8rem;">Schedule</button>
                    </div>
                </div>
            </form>
        </article>

    </section>

    {{-- Main Lead Information Grid --}}
    <section class="workspace-grid workspace-grid--2">

        {{-- Column 1: Lead Identity & Preferences --}}
        <article class="workspace-card">
            <span class="eyebrow">Contact Details</span>
            <h2 style="font-size:1.15rem; margin-bottom:0.8rem;">Personal &amp; Contact Information</h2>

            <div class="details-grid">
                <div class="details-box">
                    <span class="details-box__label">Full Name</span>
                    <span class="details-box__value">{{ $lead->name }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Email Address</span>
                    <span class="details-box__value">
                        @if($lead->email)
                            <a href="mailto:{{ $lead->email }}" style="color:#0b3668; text-decoration:none;">{{ $lead->email }}</a>
                        @else
                            <span style="color:var(--dash-shell-muted);">&mdash;</span>
                        @endif
                    </span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Phone Number</span>
                    <span class="details-box__value">
                        @if($lead->phone)
                            <a href="tel:{{ $lead->phone }}" style="color:#0b3668; text-decoration:none;">{{ $lead->phone }}</a>
                        @else
                            <span style="color:var(--dash-shell-muted);">&mdash;</span>
                        @endif
                    </span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Contact Preference</span>
                    <span class="details-box__value">{{ ucfirst($lead->contact_preference ?: 'Phone / Email') }}</span>
                </div>
            </div>

            <span class="eyebrow" style="margin-top:1.5rem;">Property &amp; Search Criteria</span>
            <h2 style="font-size:1.15rem; margin-bottom:0.8rem;">Location &amp; Requirements</h2>

            <div class="details-grid">
                <div class="details-box">
                    <span class="details-box__label">Property Address</span>
                    <span class="details-box__value">{{ $lead->property_address ?: 'Not specified' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">City, State, ZIP</span>
                    <span class="details-box__value">
                        {{ implode(', ', array_filter([$lead->city, $lead->state, $lead->zip_code])) ?: 'Not specified' }}
                    </span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Budget / Property Value</span>
                    <span class="details-box__value">
                        @if($lead->budget)
                            ${{ number_format($lead->budget) }}
                        @elseif($lead->asking_price)
                            ${{ number_format($lead->asking_price) }} (Asking)
                        @else
                            Not specified
                        @endif
                    </span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Bedrooms / Bathrooms</span>
                    <span class="details-box__value">{{ $lead->beds_baths ?: 'Not specified' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Property Type</span>
                    <span class="details-box__value">{{ $lead->property_type ?: 'Single Family Home' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Timeline</span>
                    <span class="details-box__value">{{ $lead->timeline ?: 'Immediate' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Financing Status</span>
                    <span class="details-box__value">{{ $lead->financing_status ?: 'Pre-approved' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Working with Realtor</span>
                    <span class="details-box__value">{{ $lead->working_with_realtor ? 'Yes' : 'No' }}</span>
                </div>
            </div>

            @if($lead->notes || $lead->preferences)
                <div style="margin-top:1.2rem; padding:0.9rem 1.1rem; background:var(--dash-shell-panel-soft); border-radius:12px; border:1px solid var(--dash-shell-border);">
                    <span class="details-box__label">Notes / Agent Remarks</span>
                    <p style="font-size:0.85rem; color:var(--dash-shell-text); margin:0.3rem 0 0; white-space:pre-wrap;">{{ $lead->notes ?: (is_string($lead->preferences) ? $lead->preferences : json_encode($lead->preferences)) }}</p>
                </div>
            @endif
        </article>

        {{-- Column 2: System Attributes & Linked Property --}}
        <article class="workspace-card">
            <span class="eyebrow">Source &amp; System</span>
            <h2 style="font-size:1.15rem; margin-bottom:0.8rem;">Lead Metadata &amp; Assignment</h2>

            <div class="details-grid">
                <div class="details-box">
                    <span class="details-box__label">Lead Source</span>
                    <span class="details-box__value">{{ $lead->source ?: 'OmniReferral Marketplace' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Package Tier</span>
                    <span class="details-box__value">{{ strtoupper($lead->package_type ?: 'Quick Lead') }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Assigned Agent</span>
                    <span class="details-box__value">{{ $lead->assignedAgent?->name ?: 'Unassigned' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Assigned Date</span>
                    <span class="details-box__value">{{ $lead->assigned_at?->format('M j, Y g:i A') ?: $lead->created_at->format('M j, Y') }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">First Contacted</span>
                    <span class="details-box__value">{{ $lead->contacted_at?->format('M j, Y g:i A') ?: 'Not yet contacted' }}</span>
                </div>
                <div class="details-box">
                    <span class="details-box__label">Closed Date</span>
                    <span class="details-box__value">{{ $lead->closed_at?->format('M j, Y g:i A') ?: 'Open lead' }}</span>
                </div>
            </div>

            {{-- Linked Property Section (if present) --}}
            @if($lead->property || $lead->property_image_url)
                <div style="margin-top:1.5rem; padding-top:1.2rem; border-top:1px solid var(--dash-shell-border);">
                    <span class="eyebrow">Linked Property</span>
                    <h3 style="font-size:1.05rem; margin:0.2rem 0 0.8rem;">Associated Listing</h3>

                    @if($lead->property)
                        <div style="display:flex; gap:0.9rem; align-items:center; background:var(--dash-shell-panel-soft); border:1px solid var(--dash-shell-border); border-radius:12px; padding:0.8rem;">
                            @if($lead->property->featured_image_url)
                                <img src="{{ $lead->property->featured_image_url }}" alt="{{ $lead->property->title }}" style="width:70px; height:60px; object-fit:cover; border-radius:8px;">
                            @endif
                            <div style="flex:1;">
                                <strong style="font-size:0.9rem; color:var(--dash-shell-text); display:block;">{{ $lead->property->title }}</strong>
                                <span style="font-size:0.8rem; color:var(--dash-shell-muted);">{{ $lead->property->city }}, {{ $lead->property->state }} · ${{ number_format($lead->property->price) }}</span>
                            </div>
                            <a href="{{ route('properties.show', $lead->property) }}" class="button button--ghost-blue" style="font-size:0.78rem; padding:0.35rem 0.75rem;" target="_blank">View Listing</a>
                        </div>
                    @elseif($lead->property_image_url)
                        <div style="background:var(--dash-shell-panel-soft); border:1px solid var(--dash-shell-border); border-radius:12px; padding:0.8rem;">
                            <span class="details-box__label">Property Photo Attachment</span>
                            <img src="{{ $lead->property_image_url }}" alt="Property Image" style="max-width:100%; max-height:200px; object-fit:cover; border-radius:8px; margin-top:0.4rem;">
                        </div>
                    @endif
                </div>
            @endif

            {{-- Custom Form Data / Metadata Table --}}
            @if(is_array($lead->form_data) && count($lead->form_data) > 0)
                <div style="margin-top:1.5rem; padding-top:1.2rem; border-top:1px solid var(--dash-shell-border);">
                    <span class="eyebrow">Form Submission Payload</span>
                    <h3 style="font-size:1.05rem; margin:0.2rem 0 0.8rem;">Custom Form Fields</h3>
                    <div style="background:var(--dash-shell-panel-soft); border:1px solid var(--dash-shell-border); border-radius:12px; padding:0.8rem; font-size:0.82rem;">
                        @foreach($lead->form_data as $k => $v)
                            <div style="display:flex; justify-content:space-between; padding:0.25rem 0; border-bottom:1px solid rgba(0,0,0,0.04);">
                                <span style="font-weight:600; color:var(--dash-shell-muted);">{{ Str::headline($k) }}:</span>
                                <span style="color:var(--dash-shell-text);">{{ is_array($v) ? json_encode($v) : $v }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- DNC & Legal Disclaimer --}}
            @if($lead->dnc_disclaimer)
                <div style="margin-top:1.2rem; font-size:0.75rem; color:var(--dash-shell-muted); background:rgba(0,0,0,0.02); padding:0.6rem 0.8rem; border-radius:8px;">
                    <strong>Consent &amp; DNC Disclaimer:</strong> {{ $lead->dnc_disclaimer }}
                </div>
            @endif
        </article>

    </section>

    {{-- Activity Timeline & Communication History --}}
    <section class="workspace-card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem; margin-bottom:1rem;">
            <div>
                <span class="eyebrow">Audit Trail</span>
                <h2 style="font-size:1.2rem; margin:0;">Lead Activity Timeline &amp; Communication History</h2>
            </div>
            <button type="button" onclick="openModal('addActivityModal')" class="button button--ghost-blue" style="font-size:0.82rem;">+ Log Interaction</button>
        </div>

        @if($activities->isEmpty() && $relatedMessages->isEmpty())
            <div class="workspace-empty" style="padding:1.8rem;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:#cbd5e1; margin:0 auto 0.5rem; display:block;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                No activity logged yet.<br>
                <small style="font-size:0.78rem; color:var(--dash-shell-muted);">Calls, status updates, emails, and notes will appear here automatically.</small>
            </div>
        @else
            <div class="timeline">

                {{-- Related Inbox Messages --}}
                @foreach($relatedMessages as $msg)
                    <div class="timeline-item">
                        <div class="timeline-dot timeline-dot--email"></div>
                        <div class="timeline-content">
                            <div class="timeline-meta">
                                <strong>Direct Inbox Message: {{ $msg->subject ?: 'Property Enquiry' }}</strong>
                                <span>{{ $msg->created_at->format('M j, Y g:i A') }} ({{ $msg->created_at->diffForHumans() }})</span>
                            </div>
                            <p style="margin:0; font-size:0.85rem; color:var(--dash-shell-text);">{{ $msg->message }}</p>
                            <span class="status-pill status-pill--{{ $msg->message_status === 'new' ? 'assigned' : 'neutral' }}" style="font-size:0.68rem; margin-top:0.3rem; display:inline-block;">
                                {{ ucfirst($msg->message_status) }}
                            </span>
                        </div>
                    </div>
                @endforeach

                {{-- Activity Logs --}}
                @foreach($activities as $act)
                    <div class="timeline-item">
                        <div class="timeline-dot timeline-dot--{{ strtolower($act->type) }}"></div>
                        <div class="timeline-content">
                            <div class="timeline-meta">
                                <strong>
                                    {{ Str::headline($act->type) }}
                                    @if($act->value)
                                        · {{ $act->value }}
                                    @endif
                                </strong>
                                <span>{{ $act->created_at->format('M j, Y g:i A') }} ({{ $act->created_at->diffForHumans() }})</span>
                            </div>
                            @if($act->content)
                                <p style="margin:0; font-size:0.85rem; color:var(--dash-shell-text); white-space:pre-wrap;">{{ $act->content }}</p>
                            @endif
                            @if($act->due_at)
                                <div style="font-size:0.78rem; color:#c2410c; font-weight:600; margin-top:0.3rem;">
                                    ⏰ Due: {{ $act->due_at->format('M j, Y g:i A') }}
                                </div>
                            @endif
                            <div style="font-size:0.72rem; color:var(--dash-shell-muted); margin-top:0.2rem;">
                                Logged by: {{ $act->user?->name ?: 'System' }}
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        @endif
    </section>

</div>

{{-- MODAL 1: Edit Lead Details --}}
<div id="editLeadModal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Lead Information</h3>
            <button type="button" class="modal-close" onclick="closeModal('editLeadModal')">&times;</button>
        </div>
        <form action="{{ route('agent.leads.update', $lead) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Full Name *</span>
                    <input type="text" name="name" value="{{ old('name', $lead->name) }}" required>
                </label>
                <label class="workspace-field">
                    <span>Lead Intent *</span>
                    <select name="intent" required>
                        <option value="buyer" {{ $lead->intent === 'buyer' ? 'selected' : '' }}>Buyer</option>
                        <option value="seller" {{ $lead->intent === 'seller' ? 'selected' : '' }}>Seller</option>
                        <option value="investor" {{ $lead->intent === 'investor' ? 'selected' : '' }}>Investor</option>
                        <option value="other" {{ $lead->intent === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Email Address</span>
                    <input type="email" name="email" value="{{ old('email', $lead->email) }}">
                </label>
                <label class="workspace-field">
                    <span>Phone Number</span>
                    <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Property Address</span>
                    <input type="text" name="property_address" value="{{ old('property_address', $lead->property_address) }}">
                </label>
                <label class="workspace-field">
                    <span>City</span>
                    <input type="text" name="city" value="{{ old('city', $lead->city) }}">
                </label>
                <label class="workspace-field">
                    <span>State</span>
                    <input type="text" name="state" value="{{ old('state', $lead->state) }}">
                </label>
                <label class="workspace-field">
                    <span>ZIP Code</span>
                    <input type="text" name="zip_code" value="{{ old('zip_code', $lead->zip_code) }}">
                </label>
                <label class="workspace-field">
                    <span>Budget / Value ($)</span>
                    <input type="number" name="budget" value="{{ old('budget', $lead->budget) }}" min="0">
                </label>
                <label class="workspace-field">
                    <span>Beds / Baths</span>
                    <input type="text" name="beds_baths" value="{{ old('beds_baths', $lead->beds_baths) }}" placeholder="e.g. 3 Beds / 2 Baths">
                </label>
                <label class="workspace-field">
                    <span>Property Type</span>
                    <input type="text" name="property_type" value="{{ old('property_type', $lead->property_type) }}" placeholder="e.g. Single Family, Condo">
                </label>
                <label class="workspace-field">
                    <span>Timeline</span>
                    <input type="text" name="timeline" value="{{ old('timeline', $lead->timeline) }}" placeholder="e.g. 1-3 Months">
                </label>
                <label class="workspace-field">
                    <span>Financing Status</span>
                    <input type="text" name="financing_status" value="{{ old('financing_status', $lead->financing_status) }}" placeholder="e.g. Pre-approved, Cash">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Notes / Requirements</span>
                    <textarea name="notes" rows="4">{{ old('notes', $lead->notes) }}</textarea>
                </label>
                <div class="workspace-field workspace-field--full workspace-field--actions" style="margin-top:1rem;">
                    <button type="button" class="button button--ghost-blue" onclick="closeModal('editLeadModal')">Cancel</button>
                    <button type="submit" class="button">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Add Custom Activity Log --}}
<div id="addActivityModal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Log Lead Interaction</h3>
            <button type="button" class="modal-close" onclick="closeModal('addActivityModal')">&times;</button>
        </div>
        <form action="{{ route('agent.leads.activity', $lead) }}" method="POST">
            @csrf
            <div class="workspace-form-grid">
                <label class="workspace-field">
                    <span>Interaction Type *</span>
                    <select name="type" required>
                        <option value="call">Phone Call</option>
                        <option value="email">Email Sent/Received</option>
                        <option value="sms">Text Message (SMS)</option>
                        <option value="note">General Note</option>
                        <option value="reminder">Scheduled Follow-up</option>
                        <option value="tag">Tag Update</option>
                    </select>
                </label>
                <label class="workspace-field">
                    <span>Title / Subject</span>
                    <input type="text" name="value" placeholder="e.g. Outbound Call - No Answer">
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Details / Notes</span>
                    <textarea name="content" rows="3" placeholder="Record conversation highlights or next steps..."></textarea>
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Due Date (Optional for reminders)</span>
                    <input type="datetime-local" name="due_at">
                </label>
                <div class="workspace-field workspace-field--full workspace-field--actions" style="margin-top:1rem;">
                    <button type="button" class="button button--ghost-blue" onclick="closeModal('addActivityModal')">Cancel</button>
                    <button type="submit" class="button">Save Activity</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.add('is-active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('is-active');
}

function logQuickAction(type) {
    // Post an asynchronous activity log when clicking quick call/email/sms buttons
    fetch('{{ route("agent.leads.activity", $lead) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: type,
            value: 'Initiated ' + type.toUpperCase() + ' contact',
            content: 'Agent clicked quick action button to contact lead via ' + type + '.'
        })
    }).catch(function(e) { console.error(e); });
}
</script>
@endpush
@endsection
