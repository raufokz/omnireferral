<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realtor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('brokerage_name')->nullable();
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->decimal('rating', 3, 2)->default(4.9);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('leads_closed')->default(0);
            $table->string('specialties')->nullable();
            $table->text('bio')->nullable();
            $table->string('headshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realtor_profiles');
    }
};
