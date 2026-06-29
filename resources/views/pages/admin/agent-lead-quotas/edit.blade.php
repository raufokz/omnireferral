@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit Quota - '.$quota->user->name)
@section('dashboard_description', 'Override monthly lead quota for this agent.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <div class="workspace-details">
            <div class="details-row">
                <span class="details-label">Agent</span>
                <span class="details-value">{{ $quota->user->name }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Package</span>
                <span class="details-value">{{ $quota->package?->name ?? 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Month</span>
                <span class="details-value">{{ $quota->month }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Already Assigned</span>
                <span class="details-value">{{ $quota->assigned_count }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">Current Remaining</span>
                <span class="details-value">{{ $quota->remaining_count }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.agent-lead-quotas.update', $quota) }}">
            @csrf
            @method('PATCH')
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Monthly Quota</span>
                    <input type="number" name="monthly_quota" value="{{ old('monthly_quota', $quota->monthly_quota) }}" min="0" max="9999" required>
                    @error('monthly_quota') <small class="field-error">{{ $message }}</small> @enderror
                </label>
                <div class="workspace-field workspace-field--full workspace-field--actions">
                    <button type="submit" class="button">Save</button>
                    <a href="{{ route('admin.agent-lead-quotas.index') }}" class="button button--ghost-blue">Cancel</a>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
