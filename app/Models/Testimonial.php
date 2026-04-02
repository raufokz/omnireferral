<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'company', 'location', 'photo', 'rating', 'quote', 'audio_path',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): string
    {
        if (! $this->photo) {
            return asset('images/reviews/review-1.svg');
        }

        if (Str::startsWith($this->photo, ['http://', 'https://', '/storage/', 'storage/', 'images/'])) {
            if (Str::startsWith($this->photo, 'images/')) {
                return asset($this->photo);
            }

            return Str::startsWith($this->photo, 'storage/') ? '/' . $this->photo : $this->photo;
        }

        return asset($this->photo);
    }
}
