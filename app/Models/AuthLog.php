<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthLog extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'event',
        'status',
        'ip_address',
        'user_agent',
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
     * Record an authentication event. Best-effort: never throws into the caller.
     */
    public static function record(string $event, string $status = 'info', array $attributes = []): ?self
    {
        try {
            /** @var Request|null $request */
            $request = $attributes['request'] ?? request();

            return static::create([
                'user_id'       => $attributes['user_id'] ?? null,
                'email'         => $attributes['email'] ?? null,
                'event'         => $event,
                'status'        => $status,
                'ip_address'    => $attributes['ip_address'] ?? $request?->ip(),
                'user_agent'    => $attributes['user_agent'] ?? Str::limit((string) ($request?->userAgent() ?? ''), 250, ''),
                'error_message' => isset($attributes['error_message']) ? Str::limit((string) $attributes['error_message'], 1000) : null,
                'context'       => $attributes['context'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('AuthLog::record failed.', ['error' => $e->getMessage(), 'event' => $event]);

            return null;
        }
    }
}
