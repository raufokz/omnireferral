<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('onboarding_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('onboarding_logs', 'email_sent')) {
                $table->boolean('email_sent')->default(false)->after('processed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_logs', function (Blueprint $table) {
            if (Schema::hasColumn('onboarding_logs', 'email_sent')) {
                $table->dropColumn('email_sent');
            }
        });
    }
};
