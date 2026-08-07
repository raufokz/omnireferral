<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\User;

class LeadAssignmentService
{
    /**
     * Assign a lead to an agent and record the change in lead_assignments, capturing
     * the previous realtor for history/audit purposes. This is the single place
     * assignment happens for entry points that don't need the quota-aware workflow
     * in LeadAssignmentController (the main Lead Registry quick-assign and bulk assign).
     */
    public function assign(Lead $lead, User $agent, User $actor, ?string $notes = null): LeadAssignment
    {
        $previousAgentId = $lead->assigned_agent_id;

        $lead->update([
            'assigned_agent_id' => $agent->id,
            'status' => 'assigned',
            'assigned_at' => now(),
            'assignment' => 'Assigned to ' . $agent->name,
        ]);

        return LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $agent->id,
            'assigned_by_user_id' => $actor->id,
            'previous_agent_id' => $previousAgentId,
            'package_id' => $agent->activeAgentSubscription?->package_id,
            'assignment_month' => now()->format('Y-m'),
            'assignment_status' => 'assigned',
            'sent_at' => now(),
            'assigned_at' => now(),
            'admin_notes' => $notes,
        ]);
    }
}
