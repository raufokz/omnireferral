<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'author', 'category', 'image', 'excerpt', 'content', 'meta_title', 'meta_description',
    ];

    protected $appends = ['image_url'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return asset('images/blogs/blog-1.svg');
        }

        if (Str::startsWith($this->image, ['http://', 'https://', '/storage/', 'storage/', 'images/'])) {
            if (Str::startsWith($this->image, 'images/')) {
                return asset($this->image);
            }

            return Str::startsWith($this->image, 'storage/') ? '/' . $this->image : $this->image;
        }

        return asset($this->image);
    }
}
