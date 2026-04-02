<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('category', ['lead', 'virtual_assistant'])->default('lead');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('one_time_price')->nullable();
            $table->unsignedInteger('monthly_price')->nullable();
            $table->json('features');
            $table->string('cta_label')->default('Get Started');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
