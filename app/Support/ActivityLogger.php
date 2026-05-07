<?php

namespace App\Support;

use App\Models\AdminActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Single entry point so controllers stop guessing whether to write audit_logs vs admin_activity_logs.
 *
 * Convention:
 * - AuditLog: domain/security-sensitive mutations (payments, role changes, integrations).
 * - AdminActivityLog: human-readable admin UI trail (CRUD from admin panel).
 */
final class ActivityLogger
{
    public static function domain(
        User|string|null $actor,
        string $action,
        ?Model $subject = null,
        array $context = [],
        ?string $ip = null,
    ): void {
        $userId = $actor instanceof User ? $actor->id : $actor;

        AuditLog::create([
            'user_id' => $userId ? (int) $userId : null,
            'action' => $action,
            'auditable_type' => $subject ? $subject::class : null,
            'auditable_id' => $subject?->getKey(),
            'context' => $context ?: null,
            'ip_address' => $ip ?? request()?->ip(),
        ]);
    }

    public static function adminUi(
        User|string|null $actor,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $properties = [],
        ?string $ip = null,
    ): void {
        $userId = $actor instanceof User ? $actor->id : $actor;

        AdminActivityLog::create([
            'actor_user_id' => $userId ? (int) $userId : null,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'properties' => $properties ?: null,
            'ip_address' => $ip ?? request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
