<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Lead $lead): void {
            // Keep normalized identity fields up to date for fast duplicate checks at scale.
            $lead->email_normalized = self::normalizeEmail($lead->email);
            $lead->phone_normalized = self::normalizePhone($lead->phone);
        });
    }

    protected $fillable = [
        'lead_number',
        'intent',
        'package_type',
        'package_id',
        'status',
        'source',
        'source_timestamp',
        'name',
        'email',
        'email_normalized',
        'phone',
        'phone_normalized',
        'zip_code',
        'city',
        'property_address',
        'beds_baths',
        'working_with_realtor',
        'dnc_disclaimer',
        'property_type',
        'budget',
        'dop',
        'asking_price',
        'timeline',
        'financing_status',
        'credit_score',
        'contact_preference',
        'lead_score',
        'is_priority',
        'property_image',
        'ghl_contact_id',
        'preferences',
        'notes',
        'rep_name',
        'state',
        'sent_to',
        'assignment',
        'reason_in_house',
        'realtor_response',
        'form_data',
        'route_notes',
        'property_id',
        'assigned_agent_id',
        'reviewed_by_id',
        'reviewed_at',
        'assigned_at',
        'contacted_at',
        'closed_at',
        'is_assignable',
        'row_color',
        'lead_quality_score',
    ];

    protected $casts = [
        'form_data' => 'array',
        'is_priority' => 'boolean',
        'is_assignable' => 'boolean',
        'working_with_realtor' => 'boolean',
        'source_timestamp' => 'datetime',
        'reviewed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'contacted_at' => 'datetime',
        'closed_at' => 'datetime',
        'dop' => 'date',
    ];

    /**
     * Shared status-change side effects (timestamp stamping), used by both the
     * single-lead status endpoint and bulk status updates so the behavior stays
     * identical regardless of entry point.
     *
     * @return array<string, mixed>
     */
    public static function applyStatusTimestamps(array $updates, string $status): array
    {
        if ($status === 'contacted') {
            $updates['contacted_at'] = $updates['contacted_at'] ?? now();
        }

        if ($status === 'closed') {
            $updates['closed_at'] = now();
        }

        if ($status === 'qualified') {
            $updates['reviewed_at'] = now();
        }

        return $updates;
    }

    protected $appends = ['property_image_url'];

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(LeadMatch::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function scopeDueForFollowUp($query)
    {
        return $query
            ->whereNotIn('status', ['won', 'lost', 'closed'])
            ->whereNotNull('assigned_agent_id')
            ->where(function ($q) {
                $q->whereNull('contacted_at')
                  ->orWhere('contacted_at', '<=', now()->subDays(3));
            })
            ->where(function ($q) {
                $q->where('updated_at', '<=', now()->subDays(2))
                  ->orWhere('created_at', '<=', now()->subDays(4));
            });
    }

    public function getPropertyImageUrlAttribute(): ?string
    {
        if (! $this->property_image) {
            return null;
        }

        if (Str::startsWith($this->property_image, ['http://', 'https://', '/storage/', 'storage/'])) {
            return Str::startsWith($this->property_image, 'storage/') ? '/' . $this->property_image : $this->property_image;
        }

        return Storage::url($this->property_image);
    }

    /**
     * Generate a unique human-friendly lead number.
     *
     * Format: OMNI-YYYYMMDD-XXXXXX (random uppercase base32-ish).
     * Avoids race conditions from count()+1 while staying readable.
     */
    public static function generateLeadNumber(?\DateTimeInterface $date = null): string
    {
        $stamp = ($date ? \Illuminate\Support\Carbon::instance(\Illuminate\Support\Carbon::parse($date)) : now())->format('Ymd');

        // Retry a few times in the extremely unlikely event of collision.
        for ($i = 0; $i < 8; $i++) {
            $suffix = strtoupper(Str::random(6));
            $leadNumber = "OMNI-{$stamp}-{$suffix}";

            if (! self::withTrashed()->where('lead_number', $leadNumber)->exists()) {
                return $leadNumber;
            }
        }

        // Final fallback: longer suffix.
        return "OMNI-{$stamp}-" . strtoupper(Str::random(10));
    }

    public static function normalizeEmail(?string $email): ?string
    {
        $email = Str::lower(trim((string) $email));

        return $email !== '' ? $email : null;
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if ($digits === '') {
            return null;
        }

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }

    public static function duplicateQuery(?string $email, ?string $phone)
    {
        $normalizedEmail = self::normalizeEmail($email);
        $normalizedPhone = self::normalizePhone($phone);

        if (! $normalizedEmail && ! $normalizedPhone) {
            return self::query()->whereRaw('1 = 0');
        }

        return self::query()
            ->withTrashed()
            ->where(function ($query) use ($normalizedEmail, $normalizedPhone) {
                $applied = false;

                if ($normalizedEmail) {
                    $query->where(function ($q) use ($normalizedEmail) {
                        $q->where('email_normalized', $normalizedEmail)
                            ->orWhereRaw('LOWER(email) = ?', [$normalizedEmail]);
                    });
                    $applied = true;
                }

                if ($normalizedPhone) {
                    $method = $applied ? 'orWhereRaw' : 'whereRaw';
                    $query->orWhere(function ($q) use ($normalizedPhone) {
                        $q->where('phone_normalized', $normalizedPhone)
                            ->orWhereRaw(
                                "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', ''), ' ', ''), '+', '') LIKE ?",
                                ['%' . $normalizedPhone]
                            );
                    });
                }
            });
    }

    /**
     * Canonical status list in display order, used everywhere a status dropdown
     * or per-status stat block is built (Lead Registry, manual entry, agent
     * dashboard, bulk actions) so the set of valid values only lives in one place.
     *
     * @return array<int, string>
     */
    public static function statusList(): array
    {
        return [
            'new',
            'contacted',
            'in_progress',
            'qualified',
            'assigned',
            'appointment_scheduled',
            'closed',
            'not_interested',
            'lost',
            'duplicate',
            'spam',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'not_interested' => 'Rejected',
            'in_progress' => 'In Progress',
            'appointment_scheduled' => 'Appointment Scheduled',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? Str::headline((string) $this->status);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'qualified' => 'qualified',
            'not_interested' => 'rejected',
            'lost', 'spam' => 'rejected',
            'duplicate' => 'duplicate',
            'appointment_scheduled' => 'assigned',
            default => (string) $this->status,
        };
    }

    /**
     * Human-friendly budget display. Budget is free text ("300K", "$350,000",
     * "Above 1M", "Luxury", a raw number, etc.) — format as currency only when
     * the stored value is purely numeric, otherwise show it as entered.
     */
    public function budgetLabel(): ?string
    {
        $budget = trim((string) $this->budget);

        if ($budget === '') {
            return null;
        }

        return is_numeric($budget) ? '$' . number_format((float) $budget) : $budget;
    }

    public function locationSummary(): string
    {
        return $this->property_address ?: ($this->zip_code ?: 'Location pending');
    }

    public function locationLabel(): string
    {
        return $this->property_address ? 'Property address' : 'ZIP';
    }

    public function scopeMatchingIdentityForUser($query, User $user)
    {
        $email = self::normalizeEmail($user->email);
        $phone = self::normalizePhone($user->phone);

        return $query->where(function ($q) use ($email, $phone) {
            if ($email) {
                $q->whereRaw('LOWER(TRIM(email)) = ?', [$email]);
            }

            if ($phone) {
                $method = $email ? 'orWhereRaw' : 'whereRaw';
                $q->{$method}(
                    "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', ''), ' ', ''), '+', '') LIKE ?",
                    ['%' . $phone]
                );
            }

            if (! $email && ! $phone) {
                $q->whereRaw('1 = 0');
            }
        });
    }
}
