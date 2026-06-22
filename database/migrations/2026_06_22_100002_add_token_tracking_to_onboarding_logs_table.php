<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('onboarding_logs')) {
            return;
        }

        Schema::table('onboarding_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('onboarding_logs', 'token_generated')) {
                $table->boolean('token_generated')->default(false)->after('contact_phone');
            }
            if (! Schema::hasColumn('onboarding_logs', 'token_expires_at')) {
                $table->timestamp('token_expires_at')->nullable()->after('token_generated');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('onboarding_logs')) {
            return;
        }

        Schema::table('onboarding_logs', function (Blueprint $table) {
            foreach (['token_generated', 'token_expires_at'] as $column) {
                if (Schema::hasColumn('onboarding_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
