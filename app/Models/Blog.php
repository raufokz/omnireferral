<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

        return Storage::url($this->image);
    }
}
