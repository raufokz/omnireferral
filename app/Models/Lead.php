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
        'phone',
        'zip_code',
        'property_address',
        'beds_baths',
        'working_with_realtor',
        'dnc_disclaimer',
        'property_type',
        'budget',
        'asking_price',
        'timeline',
        'financing_status',
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
        'assigned_agent_id',
        'reviewed_by_id',
        'reviewed_at',
        'assigned_at',
        'contacted_at',
        'closed_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'is_priority' => 'boolean',
        'working_with_realtor' => 'boolean',
        'source_timestamp' => 'datetime',
        'reviewed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'contacted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

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

    public function matches(): HasMany
    {
        return $this->hasMany(LeadMatch::class);
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
                    $query->whereRaw('LOWER(email) = ?', [$normalizedEmail]);
                    $applied = true;
                }

                if ($normalizedPhone) {
                    $method = $applied ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', ''), ' ', ''), '+', '') LIKE ?", ['%' . $normalizedPhone]);
                }
            });
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'not_interested' => 'Rejected',
            'in_progress' => 'In Progress',
            default => Str::headline((string) $this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'qualified' => 'qualified',
            'not_interested' => 'rejected',
            default => (string) $this->status,
        };
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
