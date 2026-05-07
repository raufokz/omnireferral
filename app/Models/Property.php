<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    public const APPROVAL_PENDING = 'pending';

    public const APPROVAL_APPROVED = 'approved';

    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'title',
        'description',
        'slug',
        'status',
        'approval_status',
        'approval_notes',
        'property_type',
        'price',
        'price_type',
        'location',
        'street_address',
        'city',
        'state',
        'country',
        'zip_code',
        'latitude',
        'longitude',
        'beds',
        'baths',
        'sqft',
        'area_size',
        'area_unit',
        'year_built',
        'parking_spaces',
        'garage_spaces',
        'furnishing_status',
        'property_condition',
        'image',
        'images',
        'video_tour_url',
        'view_360_url',
        'amenities',
        'neighborhood_info',
        'walk_score',
        'location_highlights',
        'source',
        'is_featured',
        'published_at',
        'reviewed_by_user_id',
        'reviewed_at',
        'realtor_profile_id',
        'owner_user_id',
        'listed_by_id',
    ];

    protected $casts = [
        'images' => 'array',
        'amenities' => 'array',
        'is_featured' => 'boolean',
        'is_favorited' => 'boolean',
        'area_size' => 'decimal:2',
        'published_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getRouteKey(): mixed
    {
        $slug = (string) ($this->slug ?? '');

        if ($slug !== '') {
            return $slug;
        }

        return $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $query = $this->newQuery();
        $routeField = $field ?? $this->getRouteKeyName();

        return $query
            ->where($routeField, $value)
            ->orWhere($this->getKeyName(), $value)
            ->first();
    }

    public function realtorProfile(): BelongsTo
    {
        return $this->belongsTo(RealtorProfile::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function listedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'listed_by_id');
    }

    public function resolveListedByUser(): ?User
    {
        if ($this->listed_by_id) {
            $resolved = $this->relationLoaded('listedBy')
                ? $this->listedBy
                : $this->listedBy()->first();

            if ($resolved) {
                return $resolved;
            }
        }

        if ($this->relationLoaded('realtorProfile')) {
            $realtorProfile = $this->realtorProfile;
        } elseif ($this->realtor_profile_id) {
            $realtorProfile = $this->realtorProfile()->first();
        } else {
            $realtorProfile = null;
        }

        if (! $realtorProfile) {
            return null;
        }

        if ($realtorProfile->relationLoaded('user')) {
            return $realtorProfile->user;
        }

        return $realtorProfile->user()->first();
    }

    /**
     * @return array{
     *   user_id: int|null,
     *   name: string,
     *   role_badge: string,
     *   avatar_url: string|null,
     *   avatar_initials: string,
     *   brokerage_name: string|null,
     *   city_state: string
     * }
     */
    public function listedByPresentation(): array
    {
        $realtorProfile = $this->relationLoaded('realtorProfile')
            ? $this->realtorProfile
            : ($this->realtor_profile_id ? $this->realtorProfile()->first() : null);

        $user = $this->resolveListedByUser();

        if ($user) {
            $cityState = $realtorProfile
                ? collect([$realtorProfile->service_city, $realtorProfile->service_state])
                    ->filter(fn ($p) => is_string($p) && trim($p) !== '')
                    ->implode(', ')
                : '';

            return [
                'user_id' => (int) $user->id,
                'name' => $user->publicDisplayName(),
                'role_badge' => $user->listedByRoleBadge(),
                'avatar_url' => $user->profilePhotoPublicUrl(),
                'avatar_initials' => $user->profileInitials(),
                'brokerage_name' => $realtorProfile?->brokerage_name,
                'city_state' => $cityState,
            ];
        }

        $platform = (string) config('app.name', 'OmniReferral');
        $label = $platform !== '' ? $platform : 'OmniReferral';

        return [
            'user_id' => null,
            'name' => $label,
            'role_badge' => 'Network',
            'avatar_url' => null,
            'avatar_initials' => User::initialsFromDisplayString($label),
            'brokerage_name' => null,
            'city_state' => '',
        ];
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(PropertyFavorite::class);
    }

    public function listingComments(): HasMany
    {
        return $this->hasMany(PropertyComment::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'property_favorites')
            ->withTimestamps();
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    public function scopePendingReview($query)
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    /**
     * Listings created through agent or seller dashboards (excludes seeded / marketing imports).
     */
    public function scopeUserSubmitted($query)
    {
        return $query->whereIn('source', ['Agent Dashboard Upload', 'Seller Dashboard Upload']);
    }

    public function scopeMarketplaceVisible($query)
    {
        return $query
            ->where('approval_status', self::APPROVAL_APPROVED)
            ->where('status', 'Active');
    }

    public function scopeWithFavoriteSummary($query, ?User $user = null, ?string $listingDeviceId = null)
    {
        $listingDeviceId ??= request()->attributes->get('listing_device_id');

        $query->withCount(['favorites as favorites_count']);

        $query->withExists([
            'favorites as is_favorited' => function ($favoriteQuery) use ($user, $listingDeviceId) {
                $favoriteQuery->where(function ($inner) use ($user, $listingDeviceId) {
                    if ($listingDeviceId && $user) {
                        $inner->where('device_fingerprint', $listingDeviceId)
                            ->orWhere('user_id', $user->id);

                        return;
                    }
                    if ($listingDeviceId) {
                        $inner->where('device_fingerprint', $listingDeviceId);

                        return;
                    }
                    if ($user) {
                        $inner->where('user_id', $user->id);

                        return;
                    }
                    $inner->whereRaw('1 = 0');
                });
            },
        ]);

        return $query;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    public function approvalStatusLabel(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_PENDING => 'Awaiting Review',
            self::APPROVAL_REJECTED => 'Rejected',
            default => 'Approved',
        };
    }

    public function approvalStatusTone(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_PENDING => 'pending',
            self::APPROVAL_REJECTED => 'rejected',
            default => 'qualified',
        };
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return asset('images/listings/listing-1.svg');
        }

        return $this->imageUrlFor($this->image);
    }

    public function imageUrlFor(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('images/listings/listing-1.svg');
        }

        if (Str::startsWith($path, ['http://', 'https://', '/storage/'])) {
            return $path;
        }

        if (Str::startsWith($path, 'storage/')) {
            return '/'.$path;
        }

        if (Str::startsWith($path, 'images/')) {
            return asset($path);
        }

        return Storage::url($path);
    }

    public function galleryImageUrls()
    {
        $images = collect($this->images ?? [])
            ->filter(fn ($image) => is_string($image) && trim($image) !== '');

        if ($this->image && ! $images->contains($this->image)) {
            $images->prepend($this->image);
        }

        return $images
            ->map(fn ($image) => $this->imageUrlFor($image))
            ->unique()
            ->values()
            ->whenEmpty(fn ($collection) => $collection->push($this->image_url));
    }

    public function listingIntentLabel(): string
    {
        return Str::lower((string) $this->price_type) === 'rent' ? 'For Rent' : 'For Sale';
    }

    public function formattedPrice(): string
    {
        $price = '$'.number_format((int) $this->price);

        return Str::lower((string) $this->price_type) === 'rent'
            ? $price.'/mo'
            : $price;
    }

    public function listedByLabel(): string
    {
        return $this->listedByPresentation()['name'];
    }

    public function fullAddress(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->city,
            $this->state,
            $this->country,
            $this->zip_code,
        ], fn ($value) => is_string($value) && trim($value) !== '');

        if (count($parts) > 0) {
            return implode(', ', $parts);
        }

        return (string) $this->location;
    }
}
