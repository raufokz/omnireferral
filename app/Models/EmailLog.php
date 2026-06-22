<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailLog extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'mailable',
        'subject',
        'event_type',
        'status',
        'error_message',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an email delivery outcome. Best-effort: never throws into the caller.
     */
    public static function record(string $status, array $attributes = []): ?self
    {
        try {
            $email = $attributes['email'] ?? null;

            return static::create([
                'user_id'       => $attributes['user_id'] ?? ($email ? User::where('email', $email)->value('id') : null),
                'email'         => $email,
                'mailable'      => $attributes['mailable'] ?? null,
                'subject'       => $attributes['subject'] ?? null,
                'event_type'    => $attributes['event_type'] ?? ($status === 'failed' ? 'email_failed' : 'email_sent'),
                'status'        => $status,
                'error_message' => isset($attributes['error_message']) ? Str::limit((string) $attributes['error_message'], 2000) : null,
                'context'       => $attributes['context'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('EmailLog::record failed.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public static function failed(string $email, string $error, array $attributes = []): ?self
    {
        return static::record('failed', array_merge($attributes, ['email' => $email, 'error_message' => $error]));
    }
}
