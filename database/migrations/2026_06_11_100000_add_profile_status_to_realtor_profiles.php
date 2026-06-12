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

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('realtor_profiles', 'profile_status')) {
                $table->string('profile_status', 20)->default('published')->after('headshot')->index();
            }
            if (! Schema::hasColumn('realtor_profiles', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')
                    ->nullable()
                    ->after('profile_status')
                    ->constrained('users')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('realtor_profiles', 'source_url')) {
                $table->string('source_url', 500)->nullable()->after('created_by_user_id');
            }
        });

        // Backfill: previously approved profiles become published; paid-plan agents become featured.
        if (Schema::hasColumn('realtor_profiles', 'approved_at')) {
            DB::table('realtor_profiles')
                ->whereNotNull('approved_at')
                ->whereNull('rejected_at')
                ->update(['profile_status' => 'published']);
        }

        $featuredUserIds = DB::table('users')
            ->whereNotNull('current_plan_id')
            ->pluck('id');

        if ($featuredUserIds->isNotEmpty()) {
            DB::table('realtor_profiles')
                ->whereIn('user_id', $featuredUserIds)
                ->whereIn('profile_status', ['published', 'draft'])
                ->update(['profile_status' => 'featured']);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('realtor_profiles', 'source_url')) {
                $table->dropColumn('source_url');
            }
            if (Schema::hasColumn('realtor_profiles', 'created_by_user_id')) {
                $table->dropConstrainedForeignId('created_by_user_id');
            }
            if (Schema::hasColumn('realtor_profiles', 'profile_status')) {
                $table->dropColumn('profile_status');
            }
        });
    }
};
