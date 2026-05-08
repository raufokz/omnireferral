<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExport extends Model
{
    protected $fillable = [
        'requested_by_user_id',
        'type',
        'format',
        'filters',
        'status',
        'file_path',
        'content_type',
        'file_size',
        'error',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }
}

