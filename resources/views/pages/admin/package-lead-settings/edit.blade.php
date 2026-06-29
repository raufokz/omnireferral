@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Edit '.$package->name.' Lead Settings')
@section('dashboard_description', 'Adjust lead quota and priority for this package.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <form method="POST" action="{{ route('admin.package-lead-settings.update', $package) }}">
            @csrf
            @method('PATCH')
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Monthly Lead Quota</span>
                    <input type="number" name="monthly_lead_quota" value="{{ old('monthly_lead_quota', $package->monthly_lead_quota) }}" min="0" max="9999" required>
                    <small class="muted">How many leads this package can receive per month.</small>
                    @error('monthly_lead_quota') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <label class="workspace-field workspace-field--full">
                    <span>Lead Priority</span>
                    <input type="number" name="lead_priority" value="{{ old('lead_priority', $package->lead_priority) }}" min="0" max="100" required>
                    <small class="muted">Higher priority packages get assigned leads first (Elite=3, Growth=2, Starter=1).</small>
                    @error('lead_priority') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <div class="workspace-field workspace-field--full workspace-field--actions">
                    <button type="submit" class="button">Save</button>
                    <a href="{{ route('admin.package-lead-settings.index') }}" class="button button--ghost-blue">Cancel</a>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
