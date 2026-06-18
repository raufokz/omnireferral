<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'budget_min',
        'budget_max',
        'preferred_locations',
        'bedrooms_min',
        'bathrooms_min',
        'financing_status',
        'timeline',
        'property_types',
        'has_agent',
        'agent_preference',
        'notes',
        'onboarding_data',
    ];

    protected function casts(): array
    {
        return [
            'preferred_locations' => 'array',
            'property_types' => 'array',
            'onboarding_data' => 'array',
            'has_agent' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
