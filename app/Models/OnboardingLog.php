<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingLog extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'event_type',
        'triggered_by',
        'user_action',
        'profile_action',
        'portal_access_enabled',
        'email_status',
        'email_sent_at',
        'error_message',
        'form_name',
        'form_id',
        'ghl_contact_id',
        'contact_name',
        'contact_phone',
        'payload',
        'processed_at',
        'email_sent',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'email_sent' => 'boolean',
            'portal_access_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
