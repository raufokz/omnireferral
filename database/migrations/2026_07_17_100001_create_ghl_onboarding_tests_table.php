<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ghl_onboarding_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('role')->default('agent');
            $table->string('status')->default('pending');
            $table->json('stages')->nullable();
            $table->json('form_payload')->nullable();
            $table->json('webhook_payload')->nullable();
            $table->json('webhook_response')->nullable();
            $table->json('webhook_headers')->nullable();
            $table->boolean('form_received')->default(false);
            $table->boolean('webhook_simulated')->default(false);
            $table->boolean('user_created')->default(false);
            $table->boolean('profile_created')->default(false);
            $table->boolean('password_generated')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->json('user_data')->nullable();
            $table->json('profile_data')->nullable();
            $table->string('password_token')->nullable();
            $table->string('email_status')->nullable();
            $table->string('email_recipient')->nullable();
            $table->foreignId('onboarding_log_id')->nullable()->constrained('onboarding_logs')->nullOnDelete();
            $table->foreignId('webhook_event_id')->nullable()->constrained('webhook_events')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->string('error_stage')->nullable();
            $table->string('portal_login_url')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ghl_onboarding_tests');
    }
};
