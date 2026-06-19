<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('realtor_profiles', 'is_active_agent')) {
                $table->boolean('is_active_agent')->default(true)->after('profile_status')->index();
            }

            if (! Schema::hasColumn('realtor_profiles', 'submission_source')) {
                $table->string('submission_source', 80)->nullable()->after('source_url')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('realtor_profiles')) {
            return;
        }

        Schema::table('realtor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('realtor_profiles', 'submission_source')) {
                $table->dropColumn('submission_source');
            }

            if (Schema::hasColumn('realtor_profiles', 'is_active_agent')) {
                $table->dropColumn('is_active_agent');
            }
        });
    }
};
