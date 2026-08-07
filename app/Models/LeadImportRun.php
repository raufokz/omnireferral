<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadImportRun extends Model
{
    protected $fillable = [
        'source',
        'triggered_by_user_id',
        'status',
        'created_count',
        'skipped_count',
        'failed_count',
        'warnings',
        'file_name',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'warnings' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
