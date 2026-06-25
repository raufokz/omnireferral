<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realtor_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('city');
            $table->string('state', 2);
            $table->string('primary_keyword');
            $table->text('secondary_keywords')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('og_image')->nullable();
            $table->json('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_landing_pages');
    }
};
