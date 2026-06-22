<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

/**
 * Automatically records every successfully sent email to email_logs.
 * Failures are recorded explicitly at the send sites (the framework fires no
 * "message failed" event).
 */
class LogSentEmail
{
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message; // Symfony\Component\Mime\Email
            $tos     = $message->getTo();
            $primary = $tos[0] ?? null;

            EmailLog::record('sent', [
                'email'      => $primary?->getAddress(),
                'subject'    => $message->getSubject(),
                'mailable'   => $event->data['__laravel_notification'] ?? null,
                'event_type' => 'email_sent',
                'context'    => [
                    'to' => array_map(fn ($addr) => $addr->getAddress(), $tos),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('LogSentEmail listener failed.', ['error' => $e->getMessage()]);
        }
    }
}
