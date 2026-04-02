<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\GoHighLevelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncLeadToGoHighLevel implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $leadId)
    {
    }

    public function handle(GoHighLevelService $service): void
    {
        $lead = Lead::find($this->leadId);

        if (! $lead) {
            return;
        }

        $response = $service->syncLead($lead);
        $ghlId = data_get($response, 'contact.id') ?? data_get($response, 'id');

        if ($ghlId) {
            $lead->forceFill(['ghl_contact_id' => $ghlId])->save();
        }
    }
}
