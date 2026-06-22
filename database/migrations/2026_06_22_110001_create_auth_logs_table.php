<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auth_logs')) {
            return;
        }

        Schema::create('auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            // login_success | login_failed | login_blocked_pending | login_blocked_suspended
            // | login_role_mismatch | logout | forgot_password_requested | password_reset | password_set
            $table->string('event', 50);
            $table->string('status', 20)->default('info'); // success | failure | info
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('error_message')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_logs');
    }
};
