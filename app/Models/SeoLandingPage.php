<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoLandingPage extends Model
{
    protected $fillable = [
        'realtor_profile_id', 'slug', 'city', 'state', 'primary_keyword', 'secondary_keywords',
        'seo_title', 'meta_description', 'canonical_url',
        'hero_image', 'og_image', 'content', 'is_published',
    ];

    protected $casts = [
        'content' => 'array',
        'secondary_keywords' => 'array',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(SeoLandingPageLead::class);
    }

    public function realtorProfile(): BelongsTo
    {
        return $this->belongsTo(RealtorProfile::class);
    }

    public function getSecondaryKeywordsArray(): array
    {
        $raw = $this->secondary_keywords;
        if (is_array($raw)) return $raw;
        if (is_string($raw)) return array_map('trim', explode("\n", $raw));
        return [];
    }

    public function getFaqs(): array
    {
        $faqs = $this->content['faqs'] ?? [];

        return is_array($faqs) ? $faqs : [];
    }

    public function getServiceAreas(): array
    {
        $areas = $this->content['service_areas'] ?? [];

        if (is_array($areas)) {
            return $areas;
        }

        if (is_string($areas)) {
            return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $areas))));
        }

        return [];
    }
}
