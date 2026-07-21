<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionHistory extends Model
{
    protected $table = 'subscription_history';

    protected $fillable = [
        'user_id',
        'agent_subscription_id',
        'from_package_id',
        'to_package_id',
        'action',
        'performed_by',
        'performed_by_user_id',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'from_package_id');
    }

    public function toPackage(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'to_package_id');
    }

    public function performedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'assigned' => 'Assigned',
            'upgraded' => 'Upgraded',
            'downgraded' => 'Downgraded',
            'changed' => 'Changed',
            'cancelled' => 'Cancelled',
            'reactivated' => 'Reactivated',
            default => ucfirst((string) $this->action),
        };
    }
}
