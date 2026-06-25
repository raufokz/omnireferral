<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoLandingPageLead extends Model
{
    protected $fillable = [
        'seo_landing_page_id', 'name', 'email', 'phone', 'interest', 'message',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SeoLandingPage::class, 'seo_landing_page_id');
    }
}
