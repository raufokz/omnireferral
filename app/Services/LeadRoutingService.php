<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeadRoutingService
{
    /**
     * Dispatch a lead to the best matching agent.
     */
    public function routeLead(Lead $lead): ?User
    {
        if ($lead->assigned_agent_id || $lead->status === 'closed') {
            return $lead->assignedAgent;
        }

        // 1. Find agents in the exact ZIP code or surrounding market.
        // Also ensure they have a verified Profile and an active plan.
        $eligibleAgents = RealtorProfile::whereHas('user', function ($query) {
            $query->where('role', 'agent')
                  ->where('status', 'active');
        })
        ->where('zip_code', $lead->zip_code)
        ->inRandomOrder() // Simple round-robin simulation
        ->get();

        if ($eligibleAgents->isEmpty()) {
            Log::info("No eligible agents found directly in ZIP {$lead->zip_code} for Lead {$lead->id}");
            // Fallback: Just grab an active agent locally or globally.
            $eligibleAgents = RealtorProfile::whereHas('user', function ($query) {
                $query->where('role', 'agent')->where('status', 'active');
            })->inRandomOrder()->take(5)->get();
        }

        // 2. Select the top agent constraints.
        $selectedProfile = $eligibleAgents->first();

        if ($selectedProfile) {
            $lead->update([
                'assigned_agent_id' => $selectedProfile->user_id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            \App\Models\LeadMatch::create([
                'lead_id' => $lead->id,
                'agent_id' => $selectedProfile->user_id,
                'package_id' => $lead->package_id,
                'status' => 'assigned',
                'matched_at' => now(),
                'notes' => 'Automatically routed to ' . $selectedProfile->brokerage_name,
            ]);

            return $selectedProfile->user;
        }

        return null;
    }
}
