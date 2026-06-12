<?php

// Run via: php artisan tinker --execute="require 'database/repair/agent_profile_integrity_repair.php';"
// or include it in an artisan command.

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\RealtorProfile;
use App\Models\User;

$dryRun = (bool) (filter_var($_SERVER['DRY_RUN'] ?? false, FILTER_VALIDATE_BOOL));

DB::transaction(function () use ($dryRun) {
    // 1) Orphan profiles: realtor_profiles.user_id that does not exist in users
    $orphans = DB::table('realtor_profiles')
        ->leftJoin('users', 'realtor_profiles.user_id', '=', 'users.id')
        ->whereNull('users.id')
        ->select('realtor_profiles.id as profile_id', 'realtor_profiles.user_id')
        ->get();

    if ($orphans->isNotEmpty()) {
        $profileIds = $orphans->pluck('profile_id')->all();

        if (! $dryRun) {
            // Best-effort: delete orphan profiles.
            DB::table('realtor_profiles')->whereIn('id', $profileIds)->delete();
        }

        Log::warning('[agent_profile_integrity_repair] Orphan realtor_profiles found', [
            'count' => count($profileIds),
            'profile_ids' => array_slice($profileIds, 0, 50),
            'dry_run' => $dryRun,
        ]);
    }

    // 2) Ensure 1:1 uniqueness per user by keeping the lowest id and deleting the rest.
    if (DB::getSchemaBuilder()->hasTable('realtor_profiles')) {
        $duplicates = DB::table('realtor_profiles')
            ->select('user_id', DB::raw('COUNT(*) as total'), DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $userId = (int) $dup->user_id;
            $keepId = (int) $dup->keep_id;

            $removeIds = DB::table('realtor_profiles')
                ->where('user_id', $userId)
                ->where('id', '!=', $keepId)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($removeIds === []) {
                continue;
            }

            // Re-point known foreign keys (best-effort, only when columns exist)
            if (DB::getSchemaBuilder()->hasTable('properties') && DB::getSchemaBuilder()->hasColumn('properties', 'realtor_profile_id')) {
                DB::table('properties')
                    ->whereIn('realtor_profile_id', $removeIds)
                    ->update(['realtor_profile_id' => $keepId]);
            }

            if (DB::getSchemaBuilder()->hasTable('contacts') && DB::getSchemaBuilder()->hasColumn('contacts', 'realtor_profile_id')) {
                DB::table('contacts')
                    ->whereIn('realtor_profile_id', $removeIds)
                    ->update(['realtor_profile_id' => $keepId]);
            }

            if (! $dryRun) {
                DB::table('realtor_profiles')->whereIn('id', $removeIds)->delete();
            }

            Log::warning('[agent_profile_integrity_repair] Duplicate profiles per user removed', [
                'user_id' => $userId,
                'kept_profile_id' => $keepId,
                'removed_count' => count($removeIds),
                'dry_run' => $dryRun,
            ]);
        }
    }

    // 3) Deduplicate slugs by rewriting to unique values.
    // If you already have a unique index, this will prevent insertion failures during strict enforcement.
    $slugDupes = DB::table('realtor_profiles')
        ->select('slug', DB::raw('COUNT(*) as total'))
        ->groupBy('slug')
        ->having('total', '>', 1)
        ->get();

    foreach ($slugDupes as $dupe) {
        $slug = (string) $dupe->slug;

        $rows = RealtorProfile::query()
            ->where('slug', $slug)
            ->orderBy('id')
            ->get(['id']);

        $keep = $rows->shift();
        $used = [$keep?->id];

        foreach ($rows as $row) {
            $candidate = $slug.'-'.bin2hex(random_bytes(3));

            // Ensure candidate not used
            while (RealtorProfile::query()->where('slug', $candidate)->exists()) {
                $candidate = $slug.'-'.bin2hex(random_bytes(3));
            }

            if (! $dryRun) {
                RealtorProfile::query()->whereKey((int) $row->id)->update(['slug' => $candidate]);
            }

            Log::warning('[agent_profile_integrity_repair] Slug deduped', [
                'old_slug' => $slug,
                'profile_id' => (int) $row->id,
                'new_slug' => $candidate,
                'dry_run' => $dryRun,
            ]);
        }
    }

    // 4) Null/empty bio/service fields: ensure directory filters do not break.
    // These columns are nullable in schema, but directory queries depend on non-empty values.
    // We'll leave data as-is if columns are nullable; only fix missing service_city/state/zip or bio when possible.
    $defaults = [
        'bio' => '',
        'service_city' => '',
        'service_state' => '',
        'service_zip_code' => '',
    ];

    // If columns are present, set empty string instead of NULL.
    // This makes TRIM(bio) > 0 checks behave consistently.
    $hasBio = DB::getSchemaBuilder()->hasColumn('realtor_profiles', 'bio');
    $hasCity = DB::getSchemaBuilder()->hasColumn('realtor_profiles', 'service_city');
    $hasState = DB::getSchemaBuilder()->hasColumn('realtor_profiles', 'service_state');
    $hasZip = DB::getSchemaBuilder()->hasColumn('realtor_profiles', 'service_zip_code');

    $set = [];
    if ($hasBio) $set['bio'] = DB::raw("COALESCE(bio, '')");
    if ($hasCity) $set['service_city'] = DB::raw("COALESCE(service_city, '')");
    if ($hasState) $set['service_state'] = DB::raw("COALESCE(service_state, '')");
    if ($hasZip) $set['service_zip_code'] = DB::raw("COALESCE(service_zip_code, '')");

    if ($set !== []) {
        if (! $dryRun) {
            RealtorProfile::query()->update($set);
        }

        Log::warning('[agent_profile_integrity_repair] Normalized nullable text fields to empty strings', [
            'dry_run' => $dryRun,
        ]);
    }

});

echo 'Agent profile integrity repair completed'.($dryRun ? ' (dry-run)' : '').PHP_EOL;

