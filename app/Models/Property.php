<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'status',
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
        'realtor_profile_id',
    ];

    protected $casts = [
        'images' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
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
