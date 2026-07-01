<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_landing_pages', function (Blueprint $table) {
            $table->string('realtor_photo')->nullable()->after('og_image');
        });
    }

    public function down(): void
    {
        Schema::table('seo_landing_pages', function (Blueprint $table) {
            $table->dropColumn('realtor_photo');
        });
    }
};
