<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'zip_code',
        'subject',
        'message',
        'source',
        'recipient_user_id',
        'realtor_profile_id',
        'property_id',
        'message_status',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function realtorProfile(): BelongsTo
    {
        return $this->belongsTo(RealtorProfile::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
