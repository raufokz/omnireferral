@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Assignment #'.$assignment->id)
@section('dashboard_description', 'Lead assignment details and status.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="workspace-details">
            <div class="details-row">
                <span class="details-label">Lead</span>
                <span class="details-value">{{ $assignment->lead->name ?? 'Lead #'.$assignment->lead_id }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Lead Email</span>
                <span class="details-value">{{ $assignment->lead->email ?? '—' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Lead Phone</span>
                <span class="details-value">{{ $assignment->lead->phone ?? '—' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Agent</span>
                <span class="details-value">{{ $assignment->assignedTo?->name ?? 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Assigned By</span>
                <span class="details-value">{{ $assignment->assignedBy?->name ?? 'System' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Package</span>
                <span class="details-value">{{ $assignment->package?->name ?? 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Month</span>
                <span class="details-value">{{ $assignment->assignment_month }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Status</span>
                <span class="details-value">
                    <span class="badge badge--{{ $assignment->assignment_status }}">
                        {{ str_replace('_', ' ', ucfirst($assignment->assignment_status)) }}
                    </span>
                </span>
            </div>
            <div class="details-row">
                <span class="details-label">Sent At</span>
                <span class="details-value">{{ $assignment->sent_at?->format('M j, Y g:i A') ?? '—' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Accepted At</span>
                <span class="details-value">{{ $assignment->accepted_at?->format('M j, Y g:i A') ?? '—' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Rejected At</span>
                <span class="details-value">{{ $assignment->rejected_at?->format('M j, Y g:i A') ?? '—' }}</span>
            </div>
            @if($assignment->response_from_realtor)
            <div class="details-row">
                <span class="details-label">Agent Response</span>
                <span class="details-value">{{ $assignment->response_from_realtor }}</span>
            </div>
            @endif
            @if($assignment->admin_notes)
            <div class="details-row">
                <span class="details-label">Admin Notes</span>
                <span class="details-value">{{ $assignment->admin_notes }}</span>
            </div>
            @endif
        </div>
    </section>

    <section class="workspace-card">
        <span class="eyebrow">Update Status</span>
        <h2>Change Assignment Status</h2>
        <form method="POST" action="{{ route('admin.lead-assignments.update-status', $assignment) }}">
            @csrf
            @method('PATCH')
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Status</span>
                    <select name="assignment_status" required>
                        <option value="">Select status...</option>
                        @foreach(['assigned', 'sent', 'accepted', 'rejected', 'no_response', 'reassigned', 'removed', 'closed'] as $s)
                            <option value="{{ $s }}" {{ $assignment->assignment_status === $s ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Agent Response</span>
                    <textarea name="response_from_realtor" rows="3" placeholder="Record any response from the agent...">{{ old('response_from_realtor', $assignment->response_from_realtor) }}</textarea>
                </label>
                <label class="workspace-field workspace-field--full">
                    <span>Admin Notes</span>
                    <textarea name="admin_notes" rows="3" placeholder="Additional notes...">{{ old('admin_notes', $assignment->admin_notes) }}</textarea>
                </label>
                <div class="workspace-field workspace-field--full workspace-field--actions">
                    <button type="submit" class="button">Update Status</button>
                </div>
            </div>
        </form>
    </section>

    @if(! in_array($assignment->assignment_status, ['reassigned', 'removed', 'closed']))
        <section class="workspace-card">
            <span class="eyebrow">Reassign</span>
            <h2>Move this lead to another agent</h2>
            <form method="POST" action="{{ route('admin.lead-assignments.reassign', $assignment) }}">
                @csrf
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>New agent</span>
                        <select name="agent_id" required>
                            <option value="">Select an agent...</option>
                            @foreach($reassignableAgents as $agent)
                                <option value="{{ $agent->id }}" {{ (int) $agent->id === (int) $assignment->assigned_to_user_id ? 'disabled' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('agent_id') <small class="field-error">{{ $message }}</small> @enderror
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <span>Admin notes</span>
                        <textarea name="admin_notes" rows="3" placeholder="Reason for reassignment...">{{ old('admin_notes') }}</textarea>
                    </label>
                    <label class="workspace-field workspace-field--full">
                        <label class="checkbox-label">
                            <input type="checkbox" name="override_quota" value="1" {{ old('override_quota') ? 'checked' : '' }}>
                            <span>Override quota limit</span>
                        </label>
                    </label>
                    <div class="workspace-field workspace-field--full workspace-field--actions">
                        <button type="submit" class="button">Reassign Lead</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="workspace-card">
            <span class="eyebrow">Remove</span>
            <h2>Return lead to unassigned pool</h2>
            <form method="POST" action="{{ route('admin.lead-assignments.remove', $assignment) }}"
                  onsubmit="return confirm('Remove this assignment and return the lead to the unassigned pool?');">
                @csrf
                <div class="workspace-form-grid">
                    <label class="workspace-field workspace-field--full">
                        <span>Admin notes (optional)</span>
                        <textarea name="admin_notes" rows="2" placeholder="Reason for removal...">{{ old('admin_notes') }}</textarea>
                    </label>
                    <div class="workspace-field workspace-field--full workspace-field--actions">
                        <button type="submit" class="button button--ghost-blue" style="color:#b91c1c;">Remove Assignment</button>
                    </div>
                </div>
            </form>
        </section>
    @endif

    <section class="workspace-card">
        <span class="eyebrow">History</span>
        <h2>Assignment history for this lead</h2>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Previous Realtor</th>
                        <th>Current Realtor</th>
                        <th>Assigned by</th>
                        <th>Month</th>
                        <th>Status</th>
                        <th>Sent</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $entry)
                        <tr>
                            <td>#{{ $entry->id }}</td>
                            <td>{{ $entry->previousAgent?->name ?? '— (first assignment)' }}</td>
                            <td>{{ $entry->assignedTo?->name ?? 'N/A' }}</td>
                            <td>{{ $entry->assignedBy?->name ?? 'System' }}</td>
                            <td>{{ $entry->assignment_month }}</td>
                            <td>
                                <span class="badge badge--{{ $entry->assignment_status }}">
                                    {{ str_replace('_', ' ', ucfirst($entry->assignment_status)) }}
                                </span>
                            </td>
                            <td>{{ $entry->sent_at?->format('M j, Y') ?? '—' }}</td>
                            <td>{{ $entry->admin_notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center muted">No other assignments for this lead.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <p><a href="{{ route('admin.lead-assignments.index') }}" class="link">&larr; Back to assignments</a></p>
</div>
@endsection
