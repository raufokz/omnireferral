<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'display_name',
        'email',
        'email_verified_at',
        'password',
        'must_reset_password',
        'password_set_at',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'social_facebook_url',
        'social_linkedin_url',
        'notify_email',
        'notify_marketing',
        'two_factor_enabled',
        'role',
        'staff_team',
        'status',
        'current_plan_id',
        'referred_by_user_id',
        'avatar',
        'stripe_customer_id',
        'ghl_contact_id',
        'affiliate_code',
        'onboarding_completed_at',
        'last_synced_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_reset_password' => 'boolean',
            'password_set_at' => 'datetime',
            'notify_email' => 'boolean',
            'notify_marketing' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'is_super_admin' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_super_admin ?? false);
    }

    public function publicDisplayName(): string
    {
        $display = trim((string) ($this->display_name ?? ''));

        return $display !== '' ? $display : (string) $this->name;
    }

    public function realtorProfile(): HasOne
    {
        return $this->hasOne(RealtorProfile::class);
    }

    public function buyerProfile(): HasOne
    {
        return $this->hasOne(BuyerProfile::class);
    }

    /**
     * Public agent profile URL for directory cards; null when there is no profile or approval is still pending.
     */
    public function publicAgentProfileUrl(): ?string
    {
        return $this->realtorProfile?->publicShowUrl();
    }

    public function scopeAgents($query)
    {
        return $query->where('role', 'agent');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithApprovedProfile($query)
    {
        return $query->whereHas('realtorProfile', fn ($profile) => $profile->publicVisible());
    }

    /**
     * Active agent accounts (workspace filter only — use withApprovedProfile for public directory).
     */
    public function scopePublicDirectoryAgents($query)
    {
        return $query->agents()->active();
    }

    public function agentAvatarUrl(): string
    {
        return \App\Support\AgentAvatar::url($this, $this->realtorProfile);
    }

    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_agent_id');
    }

    public function reviewedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'reviewed_by_id');
    }

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'current_plan_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_user_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by_user_id');
    }

    public function affiliateProfile(): HasOne
    {
        return $this->hasOne(AffiliateProfile::class);
    }

    public function leadMatches(): HasMany
    {
        return $this->hasMany(LeadMatch::class, 'agent_id');
    }

    public function receivedContacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'recipient_user_id');
    }

    public function submittedTestimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'submitted_by_user_id');
    }

    public function propertyFavorites(): HasMany
    {
        return $this->hasMany(PropertyFavorite::class);
    }

    public function favoriteProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_favorites')
            ->withTimestamps();
    }

    public function reviewedTestimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'reviewed_by_user_id');
    }

    public function ownedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_user_id');
    }

    public function listedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'listed_by_id');
    }

    public function enquiriesReceived(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'receiver_user_id');
    }

    public function enquiriesSent(): HasMany
    {
        return $this->hasMany(Enquiry::class, 'sender_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'staff'], true);
    }

    public function isIsa(): bool
    {
        return $this->role === 'staff' && $this->staff_team === 'isa';
    }

    public function isSales(): bool
    {
        return $this->role === 'staff' && $this->staff_team === 'sales';
    }

    public function isMarketing(): bool
    {
        return $this->role === 'staff' && $this->staff_team === 'marketing';
    }

    public function isWebDevelopment(): bool
    {
        return $this->role === 'staff' && $this->staff_team === 'web_dev';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /**
     * Workspace role helper (NOT Spatie roles).
     *
     * This project uses `users.role` as the primary workspace selector (buyer/seller/agent/admin/staff).
     * Spatie roles/permissions are used for fine-grained permissions via `$user->can('permission')`.
     */
    public function hasAnyWorkspaceRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function passwordMatches(string $plainPassword): bool
    {
        $storedPassword = (string) $this->password;

        if ($storedPassword === '') {
            return false;
        }

        return Hash::check($plainPassword, $storedPassword);
    }

    public function dashboardRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return route('super-admin.dashboard');
        }

        return match ($this->role) {
            'admin' => route('admin.dashboard'),
            'staff' => route('staff.dashboard'),
            'agent' => route('dashboard.agent'),
            'seller' => route('dashboard.seller'),
            'buyer' => route('dashboard.buyer'),
            default => route('dashboard'),
        };
    }

    public function roleLabel(): string
    {
        if ($this->role === 'staff' && $this->staff_team) {
            return match ($this->staff_team) {
                'isa' => 'ISA Staff',
                'sales' => 'Sales Staff',
                'marketing' => 'Marketing Team',
                'web_dev' => 'Web Development',
                default => 'Operations Staff',
            };
        }

        return match ($this->role) {
            'admin' => 'Admin',
            'staff' => 'Operations Staff',
            'agent' => 'Agent',
            'seller' => 'Seller',
            'buyer' => 'Buyer',
            default => ucfirst((string) $this->role),
        };
    }

    /**
     * Short role label for marketplace “Listed By” badges (Agent / Partner / Network / Admin / Referral).
     */
    public function listedByRoleBadge(): string
    {
        return match ($this->role) {
            'agent' => 'Agent',
            'seller' => 'Partner',
            'admin' => 'Admin',
            'staff' => 'Network',
            'buyer' => 'Referral',
            default => Str::title((string) $this->role),
        };
    }

    /**
     * Public URL for the user's profile photo (users.avatar only). Null when no upload.
     */
    public function profilePhotoPublicUrl(): ?string
    {
        $avatar = $this->avatar ? trim((string) $this->avatar) : '';
        if ($avatar === '') {
            return null;
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        return asset('storage/'.ltrim($avatar, '/'));
    }

    /**
     * Two-letter initials for avatar placeholders (display_name / name).
     */
    public function profileInitials(): string
    {
        return self::initialsFromDisplayString($this->publicDisplayName());
    }

    public static function initialsFromDisplayString(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) >= 2) {
            $first = mb_substr($parts[0], 0, 1);
            $last = mb_substr($parts[count($parts) - 1], 0, 1);

            return mb_strtoupper($first.$last);
        }

        return mb_strtoupper(mb_substr($name, 0, min(2, mb_strlen($name))));
    }

    /**
     * Canonical referral / affiliate code for tracking (prefer affiliate_profiles; fallback legacy column).
     */
    public function referralAffiliateCode(): ?string
    {
        $fromProfile = $this->relationLoaded('affiliateProfile')
            ? $this->affiliateProfile?->referral_code
            : $this->affiliateProfile()->value('referral_code');

        $fromProfile = $fromProfile !== null ? trim((string) $fromProfile) : '';

        if ($fromProfile !== '') {
            return $fromProfile;
        }

        $legacy = trim((string) ($this->affiliate_code ?? ''));

        return $legacy !== '' ? $legacy : null;
    }
}
