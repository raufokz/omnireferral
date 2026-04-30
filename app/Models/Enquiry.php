<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enquiry extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_REPLIED = 'replied';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'property_id',
        'contact_id',
        'sender_user_id',
        'sender_name',
        'sender_email',
        'sender_phone',
        'receiver_user_id',
        'subject',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(EnquiryReply::class)->orderBy('created_at');
    }

    public function scopeForParticipant($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('receiver_user_id', $user->id)
                ->orWhere('sender_user_id', $user->id);
        });
    }

    public function markRepliedIfNeeded(): void
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->update(['status' => self::STATUS_REPLIED]);
            $this->syncLinkedContact();
        }
    }

    public function syncLinkedContact(): void
    {
        if (! $this->contact_id) {
            return;
        }

        $messageStatus = match ($this->status) {
            self::STATUS_CLOSED => 'archived',
            self::STATUS_REPLIED => 'replied',
            default => 'new',
        };

        Contact::query()->whereKey($this->contact_id)->update(['message_status' => $messageStatus]);
    }
}
