<?php

namespace App\Support;

use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class AdminAudit
{
    public static function log(
        Request $request,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $properties = []
    ): void {
        AdminActivityLog::query()->create([
            'actor_user_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'properties' => $properties !== [] ? $properties : null,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
