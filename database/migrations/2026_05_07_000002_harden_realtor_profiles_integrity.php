<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        // Clean up any accidental duplicate profiles per user before adding a unique index.
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

            // Re-point foreign keys to the kept profile.
            if (Schema::hasTable('properties') && Schema::hasColumn('properties', 'realtor_profile_id')) {
                DB::table('properties')
                    ->whereIn('realtor_profile_id', $removeIds)
                    ->update(['realtor_profile_id' => $keepId]);
            }

            if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'realtor_profile_id')) {
                DB::table('contacts')
                    ->whereIn('realtor_profile_id', $removeIds)
                    ->update(['realtor_profile_id' => $keepId]);
            }

            DB::table('realtor_profiles')->whereIn('id', $removeIds)->delete();
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            // Ensure 1:1 user->realtor_profile at the DB layer.
            $table->unique('user_id', 'realtor_profiles_user_id_unique');

            // Optional uniqueness for license number when present.
            // Unique indexes allow multiple NULLs in MySQL, which matches "nullable but unique when set".
            if (Schema::hasColumn('realtor_profiles', 'license_number')) {
                $table->unique('license_number', 'realtor_profiles_license_number_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            $table->dropUnique('realtor_profiles_user_id_unique');

            if (Schema::hasColumn('realtor_profiles', 'license_number')) {
                $table->dropUnique('realtor_profiles_license_number_unique');
            }
        });
    }
};

