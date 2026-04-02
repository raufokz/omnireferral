<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_number',
        'intent',
        'package_type',
        'package_id',
        'status',
        'source',
        'name',
        'email',
        'phone',
        'zip_code',
        'property_type',
        'budget',
        'asking_price',
        'timeline',
        'financing_status',
        'contact_preference',
        'lead_score',
        'is_priority',
        'property_image',
        'ghl_contact_id',
        'preferences',
        'form_data',
        'route_notes',
        'assigned_agent_id',
        'reviewed_by_id',
        'reviewed_at',
        'assigned_at',
        'contacted_at',
        'closed_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'is_priority' => 'boolean',
        'reviewed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'contacted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $appends = ['property_image_url'];

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(LeadMatch::class);
    }

    public function getPropertyImageUrlAttribute(): ?string
    {
        if (! $this->property_image) {
            return null;
        }

        if (Str::startsWith($this->property_image, ['http://', 'https://', '/storage/', 'storage/'])) {
            return Str::startsWith($this->property_image, 'storage/') ? '/' . $this->property_image : $this->property_image;
        }

        return Storage::url($this->property_image);
    }
}
