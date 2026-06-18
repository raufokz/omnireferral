<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->unsignedInteger('budget_min')->nullable();
            $table->unsignedInteger('budget_max')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->unsignedTinyInteger('bedrooms_min')->nullable();
            $table->unsignedTinyInteger('bathrooms_min')->nullable();
            $table->string('financing_status', 50)->nullable();
            $table->string('timeline', 50)->nullable();
            $table->json('property_types')->nullable();
            $table->boolean('has_agent')->nullable();
            $table->string('agent_preference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->json('onboarding_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_profiles');
    }
};
