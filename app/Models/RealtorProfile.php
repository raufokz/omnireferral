<?php

namespace App\Models;

use App\Support\AgentAvatar;
use App\Support\AgentDirectory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RealtorProfile extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FEATURED = 'featured';
    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'user_id',
        'slug',
        'service_city',
        'service_state',
        'service_zip_code',
        'brokerage_name',
        'license_number',
        'rating',
        'review_count',
        'leads_closed',
        'specialties',
        'bio',
        'headshot',
        'profile_status',
        'years_of_experience',
        'languages',
        'market_areas',
        'social_links',
        'created_by_user_id',
        'source_url',
        'approved_at',
        'approved_by_user_id',
        'rejected_at',
        'rejected_by_user_id',
        'approval_notes',
    ];


    protected $casts = [
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'leads_closed' => 'integer',
        'social_links' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected $attributes = [
        'rating' => 4.5,
        'review_count' => 0,
        'leads_closed' => 0,
        'profile_status' => self::STATUS_DRAFT,
    ];


    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePublicEligible(Builder $query): Builder
    {
        return $query
            ->whereIn('profile_status', [self::STATUS_PUBLISHED, self::STATUS_FEATURED])
            ->whereNull('rejected_at');
    }

    /**
     * Public directory visibility: approved_at not null AND rejected_at null.
     */
    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->publicEligible();
    }

    /** @deprecated Use scopePublicEligible() */
    public function scopePublicDirectory(Builder $query): Builder
    {
        return $query->publicEligible();
    }

    /**
     * Top rated profiles for home/directory sections.
     */
    public function scopeTopRated(Builder $query, float $minRating = 3): Builder
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('profile_status', self::STATUS_DRAFT);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('profile_status', self::STATUS_PUBLISHED);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('profile_status', self::STATUS_FEATURED);
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('profile_status', self::STATUS_SUSPENDED);
    }

    public function scopeOrderedForDirectory(Builder $query): Builder
    {
        return AgentDirectory::applyFeaturedSort($query);
    }

    public function serviceAreaLabel(): string
    {
        return collect([$this->service_city, $this->service_state, $this->service_zip_code])
            ->filter(fn ($part) => is_string($part) && trim($part) !== '')
            ->implode(', ');
    }

    public function isPublicVisible(): bool
    {
        $accountIsActive = $this->relationLoaded('user')
            ? $this->user?->status === 'active'
            : $this->user()->where('status', 'active')->exists();

        return in_array($this->profile_status, [self::STATUS_PUBLISHED, self::STATUS_FEATURED], true)
            && $this->rejected_at === null
            && $accountIsActive;
    }

    public function isFeatured(): bool
    {
        return $this->profile_status === self::STATUS_FEATURED;
    }

    public function isDraft(): bool
    {
        return $this->profile_status === self::STATUS_DRAFT;
    }

    public function isApprovedForPublicShow(): bool
    {
        return $this->isPublicVisible();
    }


    public function headshotPublicUrl(?User $user = null): string
    {
        $user ??= $this->relationLoaded('user') ? $this->user : null;

        return AgentAvatar::url($user, $this);
    }

    public function publicShowUrl(): ?string
    {
        if (! $this->isPublicVisible()) {
            return null;
        }

        return route('agents.profile', $this);
    }

    public function specialtiesList(): array
    {
        $raw = trim((string) $this->specialties);
        if ($raw === '') {
            return [];
        }

        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public static function generateUniqueSlug(string $baseName): string
    {
        $base = Str::slug($baseName) ?: 'agent';

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $slug = $attempt === 0
                ? $base
                : $base.'-'.Str::lower(Str::random(6));

            if (! self::query()->where('slug', $slug)->exists()) {
                return $slug;
            }
        }

        return $base.'-'.Str::lower(Str::random(10));
    }

    public static function normalizeSpecialties(array|string|null $specialties): string
    {
        if (is_string($specialties)) {
            return trim($specialties);
        }

        if (! is_array($specialties)) {
            return '';
        }

        return collect($specialties)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->implode(', ');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Pending Review',
            self::STATUS_PUBLISHED => 'Approved',
            self::STATUS_FEATURED => 'Featured',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->profile_status] ?? Str::headline((string) $this->profile_status);
    }

}
