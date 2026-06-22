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
            if (! Schema::hasColumn('onboarding_logs', 'user_action')) {
                $table->string('user_action', 20)->nullable()->after('triggered_by');
            }
            if (! Schema::hasColumn('onboarding_logs', 'profile_action')) {
                $table->string('profile_action', 20)->nullable()->after('user_action');
            }
            if (! Schema::hasColumn('onboarding_logs', 'portal_access_enabled')) {
                $table->boolean('portal_access_enabled')->default(false)->after('profile_action');
            }
            if (! Schema::hasColumn('onboarding_logs', 'email_status')) {
                $table->string('email_status', 20)->default('pending')->after('portal_access_enabled');
            }
            if (! Schema::hasColumn('onboarding_logs', 'email_sent_at')) {
                $table->timestamp('email_sent_at')->nullable()->after('email_status');
            }
            if (! Schema::hasColumn('onboarding_logs', 'error_message')) {
                $table->text('error_message')->nullable()->after('email_sent_at');
            }
            if (! Schema::hasColumn('onboarding_logs', 'form_name')) {
                $table->string('form_name')->nullable()->after('error_message');
            }
            if (! Schema::hasColumn('onboarding_logs', 'form_id')) {
                $table->string('form_id')->nullable()->after('form_name');
            }
            if (! Schema::hasColumn('onboarding_logs', 'ghl_contact_id')) {
                $table->string('ghl_contact_id')->nullable()->after('form_id');
            }
            if (! Schema::hasColumn('onboarding_logs', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('ghl_contact_id');
            }
            if (! Schema::hasColumn('onboarding_logs', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('onboarding_logs')) {
            return;
        }

        Schema::table('onboarding_logs', function (Blueprint $table) {
            foreach ([
                'user_action', 'profile_action', 'portal_access_enabled', 'email_status',
                'email_sent_at', 'error_message', 'form_name', 'form_id', 'ghl_contact_id',
                'contact_name', 'contact_phone',
            ] as $column) {
                if (Schema::hasColumn('onboarding_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
