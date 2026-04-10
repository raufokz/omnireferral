<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'must_reset_password',
        'password_set_at',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
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
            'onboarding_completed_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function realtorProfile(): HasOne
    {
        return $this->hasOne(RealtorProfile::class);
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

    public function reviewedTestimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class, 'reviewed_by_user_id');
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

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function passwordMatches(string $plainPassword): bool
    {
        $storedPassword = (string) $this->password;

        if ($storedPassword === '') {
            return false;
        }

        try {
            if (Hash::check($plainPassword, $storedPassword)) {
                return true;
            }
        } catch (RuntimeException) {
            // Legacy imports may still contain plain-text passwords.
        }

        return hash_equals($storedPassword, $plainPassword);
    }

    public function passwordIsStoredAsPlainText(): bool
    {
        $storedPassword = (string) $this->password;

        if ($storedPassword === '') {
            return false;
        }

        return (($info = password_get_info($storedPassword))['algoName'] ?? 'unknown') === 'unknown';
    }

    public function upgradePlainTextPassword(string $plainPassword): void
    {
        if (! $this->passwordIsStoredAsPlainText() || ! hash_equals((string) $this->password, $plainPassword)) {
            return;
        }

        $this->forceFill([
            'password' => $plainPassword,
            'password_set_at' => now(),
        ])->save();
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'admin', 'staff' => route('admin.dashboard'),
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
}
