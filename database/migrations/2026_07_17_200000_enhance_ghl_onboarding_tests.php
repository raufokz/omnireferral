<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ghl_onboarding_tests', function (Blueprint $table) {
            $table->json('ghl_api_details')->nullable()->after('webhook_headers');
            $table->json('email_details')->nullable()->after('email_recipient');
            $table->json('subscription_details')->nullable()->after('email_details');
            $table->json('queue_details')->nullable()->after('subscription_details');
            $table->json('execution_durations')->nullable()->after('queue_details');
            $table->string('ghl_contact_id')->nullable()->after('role');
            $table->string('ghl_form_id')->nullable()->after('ghl_contact_id');
            $table->string('ghl_form_url')->nullable()->after('ghl_form_id');
            $table->string('pipeline_stage')->nullable()->after('ghl_form_url');
            $table->string('pipeline_id')->nullable()->after('pipeline_stage');
            $table->unsignedInteger('http_status')->nullable()->after('pipeline_id');
            $table->json('api_request_payload')->nullable()->after('http_status');
            $table->json('api_response_payload')->nullable()->after('api_request_payload');
            $table->unsignedBigInteger('profile_id')->nullable()->after('user_data');
            $table->boolean('profile_approved')->default(false)->after('profile_id');
            $table->boolean('profile_published')->default(false)->after('profile_approved');
            $table->unsignedBigInteger('subscription_id')->nullable()->after('profile_published');
            $table->unsignedBigInteger('package_id')->nullable()->after('subscription_id');
            $table->string('sync_user_job_id')->nullable()->after('package_id');
            $table->unsignedBigInteger('email_log_id')->nullable()->after('sync_user_job_id');
            $table->boolean('opportunity_created')->default(false)->after('email_log_id');
            $table->boolean('ghl_stage_updated')->default(false)->after('opportunity_created');
            $table->string('form_submission_method')->default('mock')->after('ghl_stage_updated');
        });
    }

    public function down(): void
    {
        Schema::table('ghl_onboarding_tests', function (Blueprint $table) {
            $table->dropColumn([
                'ghl_api_details', 'email_details', 'subscription_details',
                'queue_details', 'execution_durations', 'ghl_contact_id',
                'ghl_form_id', 'ghl_form_url', 'pipeline_stage', 'pipeline_id',
                'http_status', 'api_request_payload', 'api_response_payload',
                'profile_id', 'profile_approved', 'profile_published',
                'subscription_id', 'package_id', 'sync_user_job_id', 'email_log_id',
                'opportunity_created', 'ghl_stage_updated', 'form_submission_method',
            ]);
        });
    }
};
