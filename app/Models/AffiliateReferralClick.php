<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReferralClick extends Model
{
    protected $fillable = [
        'affiliate_profile_id',
        'referral_code',
        'ip_hash',
        'user_agent_hash',
    ];

    public function affiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class);
    }
}
