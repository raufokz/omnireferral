<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'description',
        'slug',
        'status',
        'approval_status',
        'approval_notes',
        'property_type',
        'price',
        'location',
        'zip_code',
        'latitude',
        'longitude',
        'beds',
        'baths',
        'sqft',
        'image',
        'images',
        'source',
        'is_featured',
        'published_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'realtor_profile_id',
    ];

    protected $casts = [
        'images' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function realtorProfile(): BelongsTo
    {
        return $this->belongsTo(RealtorProfile::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    public function scopePendingReview($query)
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    public function scopeMarketplaceVisible($query)
    {
        return $query
            ->where('approval_status', self::APPROVAL_APPROVED)
            ->where('status', 'Active');
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    public function approvalStatusLabel(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_PENDING => 'Awaiting Review',
            self::APPROVAL_REJECTED => 'Rejected',
            default => 'Approved',
        };
    }

    public function approvalStatusTone(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_PENDING => 'pending',
            self::APPROVAL_REJECTED => 'rejected',
            default => 'qualified',
        };
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return asset('images/listings/listing-1.svg');
        }

        if (Str::startsWith($this->image, ['http://', 'https://', '/storage/', 'storage/', 'images/'])) {
            if (Str::startsWith($this->image, 'images/')) {
                return asset($this->image);
            }

            return Str::startsWith($this->image, 'storage/') ? '/' . $this->image : $this->image;
        }

        return Storage::url($this->image);
    }
}
