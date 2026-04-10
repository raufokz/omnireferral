<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'audience',
        'company',
        'location',
        'submitted_by_email',
        'submitted_by_user_id',
        'photo',
        'rating',
        'quote',
        'audio_path',
        'video_url',
        'is_featured',
        'is_published',
        'sort_order',
        'submission_status',
        'reviewed_by_user_id',
        'reviewed_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['photo_url', 'audience_label', 'video_embed_url', 'video_playback_url', 'has_video'];

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_published', true)
            ->where('submission_status', self::STATUS_APPROVED);
    }

    public function scopePendingReview($query)
    {
        return $query->where('submission_status', self::STATUS_PENDING);
    }

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

        return Storage::url($this->photo);
    }

    public function getAudienceLabelAttribute(): string
    {
        return match ($this->audience) {
            'buyer' => 'Buyer',
            'seller' => 'Seller',
            'community' => 'Community',
            default => 'Agent',
        };
    }

    public function submissionStatusLabel(): string
    {
        return match ($this->submission_status) {
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Approved',
        };
    }

    public function submissionStatusTone(): string
    {
        return match ($this->submission_status) {
            self::STATUS_PENDING => 'pending',
            self::STATUS_REJECTED => 'rejected',
            default => 'qualified',
        };
    }

    public function getHasVideoAttribute(): bool
    {
        return $this->video_url !== null && trim((string) $this->video_url) !== '';
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1];
        }

        return null;
    }

    public function getVideoPlaybackUrlAttribute(): ?string
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return null;
        }

        if ($this->video_embed_url) {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://', '/storage/', 'storage/'])) {
            return Str::startsWith($url, 'storage/') ? '/' . $url : $url;
        }

        return Storage::url($url);
    }
}
