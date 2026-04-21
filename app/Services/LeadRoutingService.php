<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadMatch;
use App\Models\User;
use App\Notifications\NewLeadAssignedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LeadRoutingService
{
    public function __construct(
        protected LeadCustomerNotifier $leadCustomerNotifier,
    ) {
    }

    /**
     * Optionally auto-assign a new lead to an agent when enabled via config.
     */
    public function assignIfConfigured(Lead $lead): void
    {
        if ($lead->assigned_agent_id) {
            return;
        }

        if (! config('omnireferral.lead.auto_assignment_enabled')) {
            Log::info("Automatic assignment disabled for Lead {$lead->id}; manual admin assignment is required.");

            return;
        }

        $strategy = (string) config('omnireferral.lead.auto_assignment_strategy', 'round_robin');
        if ($strategy !== 'round_robin') {
            Log::warning("Unknown lead auto-assignment strategy [{$strategy}] for Lead {$lead->id}.");

            return;
        }

        $agents = User::query()
            ->where('role', 'agent')
            ->where('status', 'active')
            ->orderBy('id')
            ->pluck('id');

        if ($agents->isEmpty()) {
            Log::info("Automatic assignment skipped for Lead {$lead->id}; no active agents found.");

            return;
        }

        $cursor = (int) Cache::increment('omnireferral_lead_assignment_cursor');
        $agentId = (int) $agents[($cursor - 1) % $agents->count()];

        $previousStatus = $lead->status;

        $lead->forceFill([
            'assigned_agent_id' => $agentId,
            'status' => 'assigned',
            'assigned_at' => now(),
            'assignment' => 'Auto-assigned (round-robin)',
        ])->save();

        LeadMatch::query()->updateOrCreate(
            [
                'lead_id' => $lead->id,
                'agent_id' => $agentId,
            ],
            [
                'matched_by_id' => null,
                'package_id' => $lead->package_id,
                'status' => 'accepted',
                'location_score' => null,
                'plan_score' => null,
                'matched_at' => now(),
                'notes' => 'System round-robin auto-assignment.',
            ]
        );

        $agent = User::find($agentId);
        if ($agent) {
            $agent->notify(new NewLeadAssignedNotification($lead));
        }

        $this->leadCustomerNotifier->notifyStatusChangeIfNeeded($lead->fresh(), $previousStatus);
    }
}
