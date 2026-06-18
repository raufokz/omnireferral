<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Virtual model that surfaces GoHighLevel-specific rows from the webhook_events table.
 * No separate table — uses the existing idempotent webhook_events store.
 */
class GoHighLevelWebhookLog extends WebhookEvent
{
    protected $table = 'webhook_events';

    protected static function booted(): void
    {
        static::addGlobalScope('gohighlevel', function (Builder $builder) {
            $builder->where('provider', 'gohighlevel');
        });
    }

    /**
     * Friendly status derived from processed_at.
     */
    public function statusLabel(): string
    {
        return $this->processed_at ? 'Processed' : 'Pending';
    }

    public function statusBadgeClass(): string
    {
        return $this->processed_at ? 'workspace-pill--green' : 'workspace-pill--orange';
    }
}
