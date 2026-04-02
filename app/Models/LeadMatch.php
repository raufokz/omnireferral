<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'agent_id',
        'matched_by_id',
        'package_id',
        'status',
        'location_score',
        'plan_score',
        'matched_at',
        'responded_at',
        'notes',
    ];

    protected $casts = [
        'matched_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function matchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_by_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
