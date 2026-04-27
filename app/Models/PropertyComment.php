<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'author_name',
        'body',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function displayAuthor(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->author_name ?: 'Visitor';
    }
}
