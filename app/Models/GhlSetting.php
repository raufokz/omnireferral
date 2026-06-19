<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GhlSetting extends Model
{
    protected $table = 'ghl_settings';

    protected $fillable = [
        'api_key',
        'agency_id',
        'location_id',
        'webhook_secret',
        'environment',
        'pre_payment_survey_url',
        'post_payment_onboarding_url',
        'buyer_onboarding_form_url',
        'agent_onboarding_form_url',
        'realtor_onboarding_form_url',
        'redirect_url_after_submission',
        'hidden_fields',
        'connection_status',
        'last_tested_at',
        'last_tested_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'api_key'        => 'encrypted',
            'webhook_secret' => 'encrypted',
            'hidden_fields'  => 'array',
            'last_tested_at' => 'datetime',
        ];
    }

    public function lastTestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_tested_by_user_id');
    }

    /**
     * Return the singleton row, creating it with empty defaults if absent.
     */
    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'environment'      => 'production',
            'connection_status' => 'unknown',
            'hidden_fields'    => ['user_id', 'email', 'phone', 'name', 'role', 'plan_id', 'payment_id'],
        ]);
    }

    public function isConnected(): bool
    {
        return $this->connection_status === 'connected';
    }

    public function hasCredentials(): bool
    {
        return filled($this->api_key) && filled($this->location_id);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->connection_status) {
            'connected' => 'workspace-pill--green',
            'invalid'   => 'workspace-pill--red',
            'error'     => 'workspace-pill--orange',
            default     => 'workspace-pill--grey',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->connection_status) {
            'connected' => 'Connected',
            'invalid'   => 'Invalid Key',
            'error'     => 'Connection Error',
            default     => 'Not Tested',
        };
    }
}
