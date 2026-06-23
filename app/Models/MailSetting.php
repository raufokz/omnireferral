<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailSetting extends Model
{
    protected $table = 'mail_settings';

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'credentials_from_address',
        'credentials_from_name',
        'connection_status',
        'last_tested_at',
        'last_tested_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'password'      => 'encrypted',
            'port'          => 'integer',
            'last_tested_at' => 'datetime',
        ];
    }

    public function lastTestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_tested_by_user_id');
    }

    /**
     * Sanitise sender addresses on write so invalid formatting (<>, [], mailto:)
     * can never be persisted and break outgoing mail later.
     */
    public function setFromAddressAttribute($value): void
    {
        $this->attributes['from_address'] = \App\Support\EmailSanitizer::address($value);
    }

    public function setCredentialsFromAddressAttribute($value): void
    {
        $this->attributes['credentials_from_address'] = \App\Support\EmailSanitizer::address($value);
    }

    public static function instance(): static
    {
        return static::firstOrCreate([], [
            'mailer'       => 'smtp',
            'connection_status' => 'unknown',
        ]);
    }

    public function isConfigured(): bool
    {
        return filled($this->host) && filled($this->username) && filled($this->password);
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
            'invalid'   => 'Invalid',
            'error'     => 'Error',
            default     => 'Not Tested',
        };
    }
}
