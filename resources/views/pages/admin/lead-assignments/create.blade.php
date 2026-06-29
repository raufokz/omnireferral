@extends('layouts.dashboard')

@section('dashboard_eyebrow', 'Admin Workspace')
@section('dashboard_title', 'Assign Lead')
@section('dashboard_description', 'Manually assign a lead to an eligible agent.')

@section('content')
<div class="workspace-stack">
    <section class="workspace-card">
        <form method="POST" action="{{ route('admin.lead-assignments.store') }}">
            @csrf
            <div class="workspace-form-grid">
                <label class="workspace-field workspace-field--full">
                    <span>Lead</span>
                    <select name="lead_id" required>
                        <option value="">Select a lead...</option>
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}" {{ old('lead_id') == $lead->id ? 'selected' : '' }}>
                                {{ $lead->name ?? 'Unnamed' }} — {{ $lead->email ?? 'no email' }} ({{ $lead->status }})
                            </option>
                        @endforeach
                    </select>
                    @error('lead_id') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <label class="workspace-field workspace-field--full">
                    <span>Agent</span>
                    <select name="agent_id" required>
                        <option value="">Select an agent...</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                                @if($agent->activeAgentSubscription?->package)
                                    ({{ $agent->activeAgentSubscription->package->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('agent_id') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <label class="workspace-field workspace-field--full">
                    <span>Package (optional, defaults to agent subscription)</span>
                    <select name="package_id">
                        <option value="">Default from subscription</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} ({{ $package->monthly_lead_quota }} leads/mo, priority {{ $package->lead_priority }})
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="workspace-field workspace-field--full">
                    <span>Admin Notes</span>
                    <textarea name="admin_notes" rows="3" placeholder="Optional notes about this assignment...">{{ old('admin_notes') }}</textarea>
                    @error('admin_notes') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <label class="workspace-field workspace-field--full">
                    <label class="checkbox-label">
                        <input type="checkbox" name="override_quota" value="1" {{ old('override_quota') ? 'checked' : '' }}>
                        <span>Override quota limit (assign even if agent is at capacity)</span>
                    </label>
                </label>

                <div class="workspace-field workspace-field--full workspace-field--actions">
                    <button type="submit" class="button">Assign Lead</button>
                    <a href="{{ route('admin.lead-assignments.index') }}" class="button button--ghost-blue">Cancel</a>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
