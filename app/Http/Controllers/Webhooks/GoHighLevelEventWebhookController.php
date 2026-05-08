<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use App\Services\WebhookInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoHighLevelEventWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->isAuthorized($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $rawPayload = $request->getContent();
        $headers = collect($request->headers->all())
            ->map(fn ($values) => is_array($values) ? (count($values) === 1 ? $values[0] : $values) : $values)
            ->toArray();

        $event = (string) ($request->input('type')
            ?? $request->input('event')
            ?? data_get($payload, 'event')
            ?? data_get($payload, 'type')
            ?? 'unknown');

        $remoteId = (string) ($request->input('id')
            ?? data_get($payload, 'id')
            ?? data_get($payload, 'contact.id')
            ?? data_get($payload, 'opportunity.id')
            ?? data_get($payload, 'appointment.id')
            ?? '');

        $inbox = app(WebhookInboxService::class);
        $record = $inbox->recordInbound(
            provider: 'gohighlevel',
            event: $event !== '' ? $event : 'unknown',
            remoteId: $remoteId !== '' ? $remoteId : null,
            rawPayload: $rawPayload,
            payload: is_array($payload) ? $payload : [],
            headers: $headers,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            related: null,
        );

        return response()->json([
            'received' => true,
            'duplicate' => $record->processed_at !== null,
        ]);
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = trim((string) config('services.gohighlevel.webhook_secret'));
        $header = (string) $request->header('X-OmniReferral-Webhook', '');

        if ($secret === '') {
            return app()->environment(['local', 'testing']);
        }

        if (! hash_equals($secret, $header)) {
            return false;
        }

        if (! config('services.gohighlevel.webhook_require_nonce')) {
            return true;
        }

        $nonce = trim((string) $request->header('X-OmniReferral-Webhook-Nonce', ''));

        return $nonce !== '';
    }
}

