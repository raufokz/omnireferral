<?php

namespace App\Models;

use App\Support\AgentAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RealtorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'service_city',
        'service_state',
        'service_zip_code',
        'brokerage_name',
        'license_number',
        'years_of_experience',
        'languages',
        'market_areas',
        'social_links',
        'rating',
        'review_count',
        'leads_closed',
        'specialties',
        'bio',
        'headshot',
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
        'years_of_experience' => 'integer',
        'social_links' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeNotRejected($query)
    {
        return $query->whereNull('rejected_at');
    }

    public function scopeComplete($query)
    {
        return $query
            ->whereNotNull('service_city')
            ->where('service_city', '!=', '')
            ->whereNotNull('service_state')
            ->where('service_state', '!=', '')
            ->whereNotNull('bio')
            ->where('bio', '!=', '');
    }

    public function scopeRatingAtLeast($query, float $rating = 3.0)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Profiles eligible for the public agent directory and profile pages.
     */
    public function scopePublicEligible($query)
    {
        return $query
            ->approved()
            ->notRejected()
            ->complete()
            ->ratingAtLeast(3.0)
            ->whereHas('user', function ($userQuery) {
                $userQuery->agents()->active();
            });
    }

    /**
     * @deprecated Use scopePublicEligible() — kept for backward compatibility.
     */
    public function scopePublicDirectory($query)
    {
        return $query->publicEligible();
    }

    public function scopePendingReview($query)
    {
        return $query
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
            ->whereHas('user', fn ($userQuery) => $userQuery->agents());
    }

    public function serviceAreaLabel(): string
    {
        return collect([$this->service_city, $this->service_state, $this->service_zip_code])
            ->filter(fn ($part) => is_string($part) && trim($part) !== '')
            ->implode(', ');
    }

    public function isApprovedForPublicShow(): bool
    {
        if ($this->approved_at === null || $this->rejected_at !== null) {
            return false;
        }

        $user = $this->relationLoaded('user') ? $this->user : $this->user()->first();

        if (! $user || ! $user->isAgent() || $user->status !== 'active') {
            return false;
        }

        return $this->isComplete() && (float) $this->rating >= 3.0;
    }

    public function isComplete(): bool
    {
        return trim((string) $this->service_city) !== ''
            && trim((string) $this->service_state) !== ''
            && trim((string) $this->bio) !== '';
    }

    public function isPendingReview(): bool
    {
        return $this->approved_at === null && $this->rejected_at === null;
    }

    public function headshotPublicUrl(?User $user = null): string
    {
        $user ??= $this->relationLoaded('user') ? $this->user : null;

        return AgentAvatar::url($user, $this);
    }

    public function publicShowUrl(): ?string
    {
        if (! $this->isApprovedForPublicShow()) {
            return null;
        }

        return route('agents.show', $this);
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
}
