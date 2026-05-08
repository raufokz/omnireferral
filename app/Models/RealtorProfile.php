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
        'approved_at',
        'approved_by_user_id',
        'rejected_at',
        'rejected_by_user_id',
        'approval_notes',
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

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
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

    /**
     * Public `/agents/{slug}` page is only available after admin approval.
     */
    public function isApprovedForPublicShow(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * URL for the public agent profile, or null while approval is pending (directory may still list the user).
     */
    public function publicShowUrl(): ?string
    {
        if (! $this->isApprovedForPublicShow()) {
            return null;
        }

        return route('agents.show', $this);
    }
}
