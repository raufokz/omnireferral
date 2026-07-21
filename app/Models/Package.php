<?php

namespace App\Models;

use App\Support\PricingContent;
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
        'hourly_price',
        'stripe_price_id',
        'stripe_product_id',
        'ghl_form_url',
        'ghl_pipeline_stage',
        'features',
        'cta_label',
        'duration_days',
        'sort_order',
        'monthly_lead_quota',
        'lead_priority',
    ];

    protected $casts = [
        'features' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'monthly_lead_quota' => 'integer',
        'lead_priority' => 'integer',
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

    public function agentSubscriptions(): HasMany
    {
        return $this->hasMany(AgentSubscription::class);
    }

    public function leadAssignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function agentLeadQuotas(): HasMany
    {
        return $this->hasMany(AgentLeadQuota::class);
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

    public function displayName(): string
    {
        $pricingPlan = PricingContent::planBySlug($this->slug);

        return (string) ($pricingPlan['name'] ?? $this->name);
    }

    public function preferredCheckoutAmount(string $billing = 'auto'): ?int
    {
        $pricingPlan = PricingContent::planBySlug($this->slug);
        if ($pricingPlan) {
            $planAmount = (int) ($pricingPlan['price'] ?? 0);
            $planNote = strtolower((string) ($pricingPlan['price_note'] ?? ''));
            $planBilling = match (true) {
                str_contains($planNote, 'hour') => 'hourly',
                str_contains($planNote, 'month') => 'monthly',
                default => 'one_time',
            };

            $requestedBilling = $billing === 'auto' ? $planBilling : $billing;
            if ($planAmount > 0 && $requestedBilling === $planBilling) {
                return $planAmount;
            }
        }

        return match ($billing) {
            'hourly' => $this->hourly_price,
            'monthly' => $this->monthly_price,
            'one_time' => $this->one_time_price,
            default => $this->one_time_price ?? $this->monthly_price ?? $this->hourly_price,
        };
    }

    public function preferredCheckoutMode(string $billing = 'auto'): string
    {
        $pricingPlan = PricingContent::planBySlug($this->slug);
        if ($pricingPlan) {
            $planNote = strtolower((string) ($pricingPlan['price_note'] ?? ''));
            $planBilling = match (true) {
                str_contains($planNote, 'hour') => 'hourly',
                str_contains($planNote, 'month') => 'monthly',
                default => 'one_time',
            };
            $requested = $billing === 'auto' ? $planBilling : $billing;

            return $requested === 'monthly' ? 'subscription' : 'payment';
        }

        $requested = $billing === 'auto'
            ? ($this->monthly_price && ! $this->one_time_price && ! $this->hourly_price ? 'monthly' : 'one_time')
            : $billing;

        return $requested === 'monthly' ? 'subscription' : 'payment';
    }

    /**
     * Full capability map for this package (single source of truth for feature gating).
     *
     * @return array<string, mixed>
     */
    public function capabilities(): array
    {
        return \App\Support\PlanCapabilities::for($this->slug);
    }

    /**
     * Admin/agent-facing display label (Quick / Power / Prime naming).
     */
    public function planLabel(): string
    {
        return \App\Support\PlanCapabilities::label($this->slug);
    }

    public function listingLimit(): int
    {
        // Known plans: the capability map is authoritative.
        if (\App\Support\PlanCapabilities::isKnown($this->slug)) {
            return \App\Support\PlanCapabilities::limit($this->slug, 'listing_limit');
        }

        return $this->legacyListingLimit();
    }

    /**
     * Legacy pricing-text parsing, retained only for custom packages whose slug
     * is not part of the defined plan catalogue.
     */
    private function legacyListingLimit(): int
    {
        if ($this->category !== 'lead') {
            return 0;
        }

        $pricingPlan = PricingContent::planBySlug($this->slug);
        if ($pricingPlan) {
            $features = (array) ($pricingPlan['features'] ?? []);
            foreach ($features as $feature) {
                $feature = (string) $feature;
                if (stripos($feature, 'unlimited listings') !== false) {
                    return 10000;
                }

                if (preg_match('/\\bList\\s+up\\s+to\\s+(\\d+)\\s+active\\s+listings\\b/i', $feature, $matches)
                    || preg_match('/\\bUp\\s+to\\s+(\\d+)\\s+Active\\s+Listings\\s+Per\\s+Month\\b/i', $feature, $matches)) {
                    return (int) $matches[1];
                }
            }
        }

        return match ($this->slug) {
            'starter-leads', 'quick-leads' => 0,
            'growth-leads', 'power-leads' => 5,
            'elite-leads', 'prime-leads' => 10,
            default => 0,
        };
    }

    public function listingLimitLabel(): string
    {
        $limit = $this->listingLimit();

        return $limit > 0 ? $limit . ' active listings' : 'No listing access';
    }
}
