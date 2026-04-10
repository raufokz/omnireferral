<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('audience')->default('agent')->after('name');
            $table->string('video_url')->nullable()->after('audio_path');
            $table->boolean('is_featured')->default(false)->after('video_url');
            $table->boolean('is_published')->default(true)->after('is_featured');
            $table->unsignedInteger('sort_order')->default(0)->after('is_published');
        });

        DB::table('testimonials')
            ->whereNull('audience')
            ->update([
                'audience' => 'agent',
                'is_published' => true,
                'sort_order' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn([
                'audience',
                'video_url',
                'is_featured',
                'is_published',
                'sort_order',
            ]);
        });
    }
};
