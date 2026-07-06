<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSeoPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'seo_title',
        'meta_description',
        'canonical_url',
        'primary_keyword',
        'secondary_keywords',
        'hero_title',
        'hero_body',
        'cta_label',
        'cta_url',
        'content',
        'is_published',
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

    public function getSections(): array
    {
        $sections = $this->content['sections'] ?? [];

        return is_array($sections) ? $sections : [];
    }

    public function getFaqs(): array
    {
        $faqs = $this->content['faqs'] ?? [];

        return is_array($faqs) ? $faqs : [];
    }

    public function getSecondaryKeywordsText(): string
    {
        $keywords = $this->secondary_keywords;

        if (is_array($keywords)) {
            return implode(PHP_EOL, $keywords);
        }

        return (string) $keywords;
    }
}
