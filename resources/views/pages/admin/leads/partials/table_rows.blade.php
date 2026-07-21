@forelse($leads as $lead)
    @php
        $statusTone = $lead->statusTone();
        $statusLabel = $lead->statusLabel();
        $priceSummary = $lead->budget
            ? 'Budget $' . number_format($lead->budget)
            : ($lead->asking_price ? 'Ask $' . number_format($lead->asking_price) : 'N/A');
    @endphp
    <tr data-lead-id="{{ $lead->id }}">
        <td data-label="Lead">
            <strong>{{ $lead->name }}</strong>
            <div class="workspace-property__meta">{{ $lead->email ?: 'No email' }}</div>
            <div class="workspace-property__meta">{{ $lead->phone ?: 'No phone' }} · {{ $lead->lead_number }}</div>
        </td>
        <td data-label="Stage">
            <span class="status-pill status-pill--{{ $statusTone }}">{{ $statusLabel }}</span>
            <div class="workspace-property__meta">{{ ucfirst($lead->intent) }} · {{ \Illuminate\Support\Str::headline($lead->source ?: 'Web') }}</div>
            <form action="{{ route('admin.leads.status', $lead) }}" method="POST" class="js-loading-form" style="margin-top: 0.45rem;">
                @csrf
                <select name="status" onchange="this.form.submit()">
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>
                            {{ $status === 'not_interested' ? 'Rejected' : ucfirst(str_replace('_',' ', $status)) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </td>
        <td data-label="Market">
            <strong>{{ $lead->property_address ?: 'No address' }}</strong>
            <div class="workspace-property__meta">{{ $lead->zip_code ?: 'N/A' }} · {{ $lead->state ?: 'N/A' }}</div>
            <div class="workspace-property__meta">{{ $priceSummary }}</div>
        </td>
        <td data-label="Ops">
            <strong>{{ $lead->rep_name ?: 'No rep' }}</strong>
            <div class="workspace-property__meta">Sent: {{ $lead->sent_to ?: 'N/A' }}</div>
            <div class="workspace-property__meta">{{ \Illuminate\Support\Str::limit($lead->reason_in_house ?: 'No reason', 42) }}</div>
        </td>
        <td data-label="Assignment">
            <span class="status-pill status-pill--{{ $lead->assigned_agent_id ? 'assigned' : 'new' }}">{{ $lead->assignedAgent?->name ?? 'Unassigned' }}</span>
            <div class="workspace-property__meta">{{ $lead->assigned_at ? $lead->assigned_at->diffForHumans() : 'Awaiting routing' }}</div>
            <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="js-loading-form" style="margin-top: 0.45rem;">
                @csrf
                <div class="workspace-actions">
                    <select name="agent_id" required>
                        <option value="">Agent...</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ (int) $lead->assigned_agent_id === (int) $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="button button--ghost-blue js-loading-btn">Set</button>
                </div>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5">
            <div class="workspace-empty">No registry records found for the current filter.</div>
        </td>
    </tr>
@endforelse
