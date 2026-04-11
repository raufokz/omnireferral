<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeadRoutingService
{
    /**
     * Preserve the admin-only assignment policy for leads.
     */
    public function routeLead(Lead $lead): ?User
    {
        if ($lead->assigned_agent_id) {
            return $lead->assignedAgent;
        }

        Log::info("Automatic assignment skipped for Lead {$lead->id}; manual admin assignment is required.");
        return null;
    }
}
