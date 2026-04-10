<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'category',
        'billing_type',
        'is_featured',
        'is_active',
        'one_time_price',
        'monthly_price',
        'stripe_price_id',
        'stripe_product_id',
        'ghl_form_url',
        'ghl_pipeline_stage',
        'features',
        'cta_label',
        'duration_days',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(User::class, 'current_plan_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function routeMatches(): HasMany
    {
        return $this->hasMany(LeadMatch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLeadPlans($query)
    {
        return $query->where('category', 'lead');
    }

    public function scopeAssistantPlans($query)
    {
        return $query->where('category', 'virtual_assistant');
    }

    public function preferredCheckoutAmount(string $billing = 'auto'): ?int
    {
        return match ($billing) {
            'monthly' => $this->monthly_price,
            'one_time' => $this->one_time_price,
            default => $this->one_time_price ?? $this->monthly_price,
        };
    }

    public function preferredCheckoutMode(string $billing = 'auto'): string
    {
        $requested = $billing === 'auto'
            ? ($this->monthly_price && ! $this->one_time_price ? 'monthly' : 'one_time')
            : $billing;

        return $requested === 'monthly' ? 'subscription' : 'payment';
    }

    public function listingLimit(): int
    {
        if ($this->category !== 'lead') {
            return 0;
        }

        return match ($this->slug) {
            'quick-leads' => 5,
            'power-leads' => 15,
            'prime-leads' => 35,
            default => 0,
        };
    }

    public function listingLimitLabel(): string
    {
        $limit = $this->listingLimit();

        return $limit > 0 ? $limit . ' active listings' : 'No listing access';
    }
}
