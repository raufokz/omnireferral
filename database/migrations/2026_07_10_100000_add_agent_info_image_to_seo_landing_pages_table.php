<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_landing_pages', function (Blueprint $table) {
            $table->string('agent_info_image')->nullable()->after('realtor_photo');
        });
    }

    public function down(): void
    {
        Schema::table('seo_landing_pages', function (Blueprint $table) {
            $table->dropColumn('agent_info_image');
        });
    }
};
