<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
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

        WebhookEvent::create([
            'provider' => 'gohighlevel',
            'event' => $event !== '' ? $event : 'unknown',
            'remote_id' => $remoteId !== '' ? $remoteId : null,
            'headers' => $headers,
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'processed_at' => null,
        ]);

        return response()->json(['received' => true]);
    }

    private function isAuthorized(Request $request): bool
    {
        $secret = trim((string) config('services.gohighlevel.webhook_secret'));
        $header = (string) $request->header('X-OmniReferral-Webhook', '');

        if ($secret === '') {
            return app()->environment(['local', 'testing']);
        }

        return hash_equals($secret, $header);
    }
}

