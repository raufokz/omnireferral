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
        'user_id', 'slug', 'brokerage_name', 'city', 'state', 'zip_code', 'rating', 'review_count', 'leads_closed', 'specialties', 'bio', 'headshot',
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
}
