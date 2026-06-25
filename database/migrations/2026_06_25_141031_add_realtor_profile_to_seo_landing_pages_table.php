<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seo_landing_pages') || Schema::hasColumn('seo_landing_pages', 'realtor_profile_id')) {
            return;
        }

        Schema::table('seo_landing_pages', function (Blueprint $table) {
            $table->foreignId('realtor_profile_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('seo_landing_pages') || ! Schema::hasColumn('seo_landing_pages', 'realtor_profile_id')) {
            return;
        }

        Schema::table('seo_landing_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('realtor_profile_id');
        });
    }
};
