<?php

namespace App\Services;

use App\Models\WebhookEvent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WebhookInboxService
{
    /**
     * Record an inbound webhook request in the database.
     *
     * Returns the existing row if this webhook is a duplicate (idempotency).
     */
    public function recordInbound(
        string $provider,
        string $event,
        ?string $remoteId,
        ?string $rawPayload,
        array $payload,
        array $headers,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?array $related = null,
    ): WebhookEvent {
        $remoteId = $remoteId !== null ? trim($remoteId) : null;
        if ($remoteId === '') {
            $remoteId = null;
        }

        $payloadHash = $rawPayload !== null && $rawPayload !== ''
            ? hash('sha256', $rawPayload)
            : null;

        // Optional explicit nonce header (allows strict replay prevention even if payload can vary).
        $nonce = null;
        if (isset($headers['x-omnireferral-webhook-nonce']) && is_string($headers['x-omnireferral-webhook-nonce'])) {
            $nonce = trim($headers['x-omnireferral-webhook-nonce']);
        } elseif (isset($headers['X-OmniReferral-Webhook-Nonce']) && is_string($headers['X-OmniReferral-Webhook-Nonce'])) {
            $nonce = trim($headers['X-OmniReferral-Webhook-Nonce']);
        }

        if ($nonce !== null && $nonce !== '') {
            // Fold the nonce into payload hash to make replay impossible without DB access.
            $payloadHash = hash('sha256', ($payloadHash ?? '') . '|' . $nonce);
        }

        try {
            return WebhookEvent::create([
                'provider' => $provider,
                'event' => $event !== '' ? $event : 'unknown',
                'remote_id' => $remoteId,
                'payload_hash' => $payloadHash,
                'headers' => $headers,
                'payload' => $payload,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'related_type' => $related['type'] ?? null,
                'related_id' => $related['id'] ?? null,
                'processed_at' => null,
            ]);
        } catch (QueryException $e) {
            // Unique constraint hit: treat as duplicate (idempotent).
            $existing = WebhookEvent::query()
                ->where('provider', $provider)
                ->where('event', $event !== '' ? $event : 'unknown')
                ->when($remoteId !== null, fn ($q) => $q->where('remote_id', $remoteId))
                ->when($remoteId === null && $payloadHash !== null, fn ($q) => $q->where('payload_hash', $payloadHash))
                ->latest('id')
                ->first();

            if ($existing) {
                return $existing;
            }

            Log::warning('Webhook inbox dedupe failed to locate existing row after unique violation.', [
                'provider' => $provider,
                'event' => $event,
                'remote_id' => $remoteId,
                'payload_hash' => $payloadHash,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function markProcessed(WebhookEvent $event): void
    {
        if ($event->processed_at) {
            return;
        }

        $event->forceFill(['processed_at' => Carbon::now()])->save();
    }
}

