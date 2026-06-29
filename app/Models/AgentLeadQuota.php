<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentLeadQuota extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'month',
        'monthly_quota',
        'assigned_count',
        'remaining_count',
        'overdue_count',
    ];

    protected $casts = [
        'monthly_quota' => 'integer',
        'assigned_count' => 'integer',
        'remaining_count' => 'integer',
        'overdue_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function scopeForMonth($query, string $month)
    {
        return $query->where('month', $month);
    }

    public function scopeHasRemaining($query)
    {
        return $query->where('remaining_count', '>', 0);
    }
}
