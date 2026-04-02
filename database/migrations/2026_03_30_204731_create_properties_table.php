<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('Active');
            $table->string('property_type');
            $table->unsignedInteger('price');
            $table->string('location');
            $table->string('zip_code', 10);
            $table->unsignedTinyInteger('beds')->default(3);
            $table->decimal('baths', 3, 1)->default(2.0);
            $table->unsignedInteger('sqft')->default(1500);
            $table->string('image')->nullable();
            $table->string('source')->default('OmniReferral Network');
            $table->foreignId('realtor_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
