<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ghl_settings', function (Blueprint $table) {
            $table->id();
            $table->text('api_key')->nullable();
            $table->string('agency_id', 100)->nullable();
            $table->string('location_id', 100)->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('environment', 20)->default('production');
            $table->string('pre_payment_survey_url')->nullable();
            $table->string('post_payment_onboarding_url')->nullable();
            $table->string('buyer_onboarding_form_url')->nullable();
            $table->string('agent_onboarding_form_url')->nullable();
            $table->string('realtor_onboarding_form_url')->nullable();
            $table->string('redirect_url_after_submission')->nullable();
            $table->json('hidden_fields')->nullable();
            $table->string('connection_status', 20)->default('unknown');
            $table->timestamp('last_tested_at')->nullable();
            $table->foreignId('last_tested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ghl_settings');
    }
};
