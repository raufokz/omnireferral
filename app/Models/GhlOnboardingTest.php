<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class GhlOnboardingTest extends Model
{
    protected $fillable = [
        'user_id', 'email', 'role', 'ghl_contact_id', 'ghl_form_id', 'ghl_form_url',
        'pipeline_stage', 'pipeline_id', 'http_status', 'status', 'form_submission_method',
        'stages', 'form_payload', 'webhook_payload', 'webhook_response', 'webhook_headers',
        'ghl_api_details', 'email_details', 'subscription_details', 'queue_details',
        'execution_durations', 'api_request_payload', 'api_response_payload',
        'form_received', 'webhook_simulated', 'user_created', 'profile_created',
        'password_generated', 'email_sent', 'opportunity_created', 'ghl_stage_updated',
        'user_data', 'profile_data', 'profile_id', 'profile_approved', 'profile_published',
        'password_token', 'email_status', 'email_recipient', 'email_log_id',
        'onboarding_log_id', 'webhook_event_id', 'subscription_id', 'package_id',
        'sync_user_job_id', 'error_message', 'error_stage', 'portal_login_url',
        'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'stages' => 'array',
            'form_payload' => 'array',
            'webhook_payload' => 'array',
            'webhook_response' => 'array',
            'webhook_headers' => 'array',
            'ghl_api_details' => 'array',
            'email_details' => 'array',
            'subscription_details' => 'array',
            'queue_details' => 'array',
            'execution_durations' => 'array',
            'api_request_payload' => 'array',
            'api_response_payload' => 'array',
            'user_data' => 'array',
            'profile_data' => 'array',
            'form_received' => 'boolean',
            'webhook_simulated' => 'boolean',
            'user_created' => 'boolean',
            'profile_created' => 'boolean',
            'password_generated' => 'boolean',
            'email_sent' => 'boolean',
            'opportunity_created' => 'boolean',
            'ghl_stage_updated' => 'boolean',
            'profile_approved' => 'boolean',
            'profile_published' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function onboardingLog(): BelongsTo
    {
        return $this->belongsTo(OnboardingLog::class);
    }

    public function webhookEvent(): BelongsTo
    {
        return $this->belongsTo(WebhookEvent::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(RealtorProfile::class, 'profile_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(AgentSubscription::class, 'subscription_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function emailLog(): BelongsTo
    {
        return $this->belongsTo(EmailLog::class, 'email_log_id');
    }

    public function stageStatus(string $stage): string
    {
        return data_get($this->stages, "$stage.status", 'pending');
    }

    public function stageData(string $stage): mixed
    {
        return data_get($this->stages, "$stage.data");
    }

    public function stageError(string $stage): ?string
    {
        return data_get($this->stages, "$stage.error");
    }

    public function stageTimestamp(string $stage): ?string
    {
        return data_get($this->stages, "$stage.timestamp");
    }

    public function stageDuration(string $stage): ?float
    {
        return data_get($this->execution_durations, $stage);
    }

    public function isComplete(): bool
    {
        return $this->form_received
            && $this->user_created
            && $this->profile_created
            && $this->password_generated;
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completed' => 'workspace-pill--green',
            'failed' => 'workspace-pill--red',
            'processing' => 'workspace-pill--orange',
            default => 'workspace-pill--grey',
        };
    }

    public function usedRealForm(): bool
    {
        return $this->form_submission_method === 'real_form';
    }

    public function usedWebhook(): bool
    {
        return $this->form_submission_method === 'webhook';
    }

    public function totalDuration(): ?float
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }
        return $this->started_at->diffInSeconds($this->completed_at, true);
    }

    public function maskedPasswordToken(): ?string
    {
        $token = $this->password_token;
        if (! $token) {
            return null;
        }
        return substr($token, 0, 8) . '…' . substr($token, -4);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('email', 'like', "%{$term}%")
              ->orWhere('ghl_contact_id', 'like', "%{$term}%")
              ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
        });
    }

    public function scopeByStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', Carbon::parse($from));
        }
        if ($to) {
            $query->whereDate('created_at', '<=', Carbon::parse($to));
        }
        return $query;
    }
}
