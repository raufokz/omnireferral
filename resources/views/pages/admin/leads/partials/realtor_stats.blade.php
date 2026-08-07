@php
    $statLabels = [
        'new' => 'New Leads',
        'contacted' => 'Contacted',
        'in_progress' => 'In Progress',
        'qualified' => 'Qualified',
        'assigned' => 'Assigned',
        'appointment_scheduled' => 'Appointment Scheduled',
        'closed' => 'Closed',
        'not_interested' => 'Rejected',
        'lost' => 'Lost',
        'duplicate' => 'Duplicate',
        'spam' => 'Spam',
    ];
@endphp

<section class="workspace-card">
    <span class="eyebrow">Realtor Performance</span>
    <h2>{{ $realtorStats['agent_name'] }} — Lead Breakdown</h2>

    <div class="workspace-grid workspace-grid--4" style="margin-top: 0.75rem;">
        <article class="workspace-card workspace-kpi">
            <span>Total Leads</span>
            <strong>{{ number_format($realtorStats['total']) }}</strong>
            <span>Assigned to this realtor</span>
        </article>
        @foreach($statLabels as $statusKey => $label)
            @php $stat = $realtorStats['by_status'][$statusKey] ?? ['count' => 0, 'percent' => 0]; @endphp
            <article class="workspace-card workspace-kpi">
                <span>{{ $label }}</span>
                <strong>{{ number_format($stat['count']) }}</strong>
                <span>{{ $stat['percent'] }}% of total</span>
            </article>
        @endforeach
    </div>
</section>
