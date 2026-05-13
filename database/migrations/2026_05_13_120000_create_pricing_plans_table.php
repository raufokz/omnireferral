<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['real_estate', 'virtual_assistance'])->default('real_estate');
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tier')->nullable();
            $table->unsignedInteger('value_price')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->string('price_note')->nullable();
            $table->text('summary')->nullable();
            $table->json('features')->nullable();
            $table->string('cta_label')->default('Get Started');
            $table->string('cta_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
