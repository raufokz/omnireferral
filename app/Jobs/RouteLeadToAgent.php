<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadRoutingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RouteLeadToAgent implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * Backoff schedule in seconds.
     *
     * @var array<int>
     */
    public array $backoff = [30, 120, 300];

    public int $timeout = 15;

    public function __construct(public int $leadId)
    {
    }

    public function handle(LeadRoutingService $routingService): void
    {
        $lead = Lead::find($this->leadId);

        if (! $lead) {
            return;
        }

        if ($lead->assigned_agent_id) {
            Log::info("Lead {$lead->id} is already assigned.");

            return;
        }

        try {
            $routingService->assignIfConfigured($lead);
        } catch (\Throwable $e) {
            Log::error('RouteLeadToAgent job threw exception.', [
                'lead_id' => $this->leadId,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }

        if ($lead->fresh()->assigned_agent_id) {
            Log::info("Lead {$lead->id} was routed via auto-assignment rules.");
        } else {
            Log::info("Lead {$lead->id} remains unassigned (auto-assignment disabled or no eligible agents).");
        }
    }
}
