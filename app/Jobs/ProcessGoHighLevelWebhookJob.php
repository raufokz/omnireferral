<?php

namespace App\Jobs;

use App\Models\WebhookEvent;
use App\Services\OnboardingSyncService;
use App\Services\WebhookInboxService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessGoHighLevelWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 90;

    public function __construct(
        public readonly int $webhookEventId,
    ) {}

    public function handle(OnboardingSyncService $syncService, WebhookInboxService $inbox): void
    {
        $record = WebhookEvent::find($this->webhookEventId);

        if (! $record) {
            Log::warning('ProcessGoHighLevelWebhookJob: webhook event not found.', [
                'webhook_event_id' => $this->webhookEventId,
            ]);

            return;
        }

        if ($record->processed_at) {
            return; // Idempotent: already processed
        }

        if ($record->event !== 'onboarding_completed') {
            Log::info('ProcessGoHighLevelWebhookJob: skipping non-onboarding event.', [
                'event' => $record->event,
            ]);

            return;
        }

        $payload    = is_array($record->payload) ? $record->payload : (json_decode((string) $record->payload, true) ?? []);
        $userId     = isset($payload['field_user_id']) ? (int) $payload['field_user_id'] : null;

        try {
            $result = DB::transaction(fn () => $syncService->sync($payload, $userId));

            $user = $result['user'];

            $inbox->markProcessed($record);

            if ($result['isFirstOnboarding'] && $result['plainPassword']) {
                SendPortalLoginAccessEmailJob::dispatch(
                    userId: $user->id,
                    plainPassword: $result['plainPassword'],
                    loginUrl: route('login'),
                    dashboardUrl: $user->dashboardRoute(),
                );
            }

            SyncUserToGoHighLevel::dispatch($user->id);

            Log::info('ProcessGoHighLevelWebhookJob: onboarding processed.', [
                'webhook_event_id'  => $this->webhookEventId,
                'user_id'           => $user->id,
                'first_onboarding'  => $result['isFirstOnboarding'],
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessGoHighLevelWebhookJob failed.', [
                'webhook_event_id' => $this->webhookEventId,
                'error'            => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessGoHighLevelWebhookJob permanently failed.', [
            'webhook_event_id' => $this->webhookEventId,
            'error'            => $exception->getMessage(),
        ]);
    }
}
