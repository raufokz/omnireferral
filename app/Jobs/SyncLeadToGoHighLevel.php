<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\GoHighLevelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncLeadToGoHighLevel implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * Backoff schedule in seconds.
     *
     * @var array<int>
     */
    public array $backoff = [60, 300, 900, 1800];

    public int $timeout = 20;

    public function __construct(public int $leadId)
    {
    }

    public function handle(GoHighLevelService $service): void
    {
        $lead = Lead::find($this->leadId);

        if (! $lead) {
            return;
        }

        try {
            $response = $service->syncLead($lead);
        } catch (\Throwable $e) {
            Log::error('SyncLeadToGoHighLevel job threw exception.', [
                'lead_id' => $this->leadId,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }

        if (! $response) {
            // Returning without throwing keeps the job "successful". For production reliability we want retries.
            throw new \RuntimeException("GoHighLevel syncLead failed for lead_id={$this->leadId}");
        }

        $ghlId = data_get($response, 'contact.id') ?? data_get($response, 'id');

        if ($ghlId) {
            $lead->forceFill(['ghl_contact_id' => $ghlId])->save();
        }
    }
}
