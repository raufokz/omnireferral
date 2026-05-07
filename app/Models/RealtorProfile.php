<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RealtorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'service_city',
        'service_state',
        'service_zip_code',
        'brokerage_name',
        'license_number',
        'rating',
        'review_count',
        'leads_closed',
        'specialties',
        'bio',
        'headshot',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'leads_closed' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Public directory scope: only active agent users.
     */
    public function scopePublicDirectory($query)
    {
        return $query
            ->whereNotNull('approved_at')
            ->whereHas('user', function ($q) {
            $q->where('role', 'agent')->where('status', 'active');
        });
    }

    public function serviceAreaLabel(): string
    {
        return collect([$this->service_city, $this->service_state, $this->service_zip_code])
            ->filter(fn ($p) => is_string($p) && trim($p) !== '')
            ->implode(', ');
    }
}
