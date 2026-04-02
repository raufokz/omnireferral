<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('billing_type')->default('hybrid')->after('category');
            $table->string('stripe_price_id')->nullable()->after('monthly_price');
            $table->string('stripe_product_id')->nullable()->after('stripe_price_id');
            $table->string('ghl_form_url')->nullable()->after('stripe_product_id');
            $table->string('ghl_pipeline_stage')->nullable()->after('ghl_form_url');
            $table->boolean('is_active')->default(true)->after('is_featured');
            $table->unsignedInteger('duration_days')->default(30)->after('cta_label');
            $table->unsignedSmallInteger('sort_order')->default(1)->after('duration_days');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_plan_id')->nullable()->after('status')->constrained('packages')->nullOnDelete();
            $table->foreignId('referred_by_user_id')->nullable()->after('current_plan_id')->constrained('users')->nullOnDelete();
            $table->string('staff_team')->nullable()->after('role');
            $table->string('stripe_customer_id')->nullable()->after('avatar')->index();
            $table->string('ghl_contact_id')->nullable()->after('stripe_customer_id')->index();
            $table->string('affiliate_code')->nullable()->after('ghl_contact_id')->unique();
            $table->timestamp('onboarding_completed_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_synced_at')->nullable()->after('onboarding_completed_at');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('package_type')->constrained('packages')->nullOnDelete();
            $table->foreignId('reviewed_by_id')->nullable()->after('assigned_agent_id')->constrained('users')->nullOnDelete();
            $table->string('source')->default('website')->after('status');
            $table->string('timeline')->nullable()->after('asking_price');
            $table->string('financing_status')->nullable()->after('timeline');
            $table->string('contact_preference')->nullable()->after('financing_status');
            $table->unsignedTinyInteger('lead_score')->nullable()->after('contact_preference');
            $table->boolean('is_priority')->default(false)->after('lead_score');
            $table->string('ghl_contact_id')->nullable()->after('property_image')->index();
            $table->json('form_data')->nullable()->after('preferences');
            $table->text('route_notes')->nullable()->after('form_data');
            $table->timestamp('reviewed_at')->nullable()->after('route_notes');
            $table->timestamp('assigned_at')->nullable()->after('reviewed_at');
            $table->timestamp('contacted_at')->nullable()->after('assigned_at');
            $table->timestamp('closed_at')->nullable()->after('contacted_at');
            $table->softDeletes();
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->json('images')->nullable()->after('image');
            $table->decimal('latitude', 10, 7)->nullable()->after('zip_code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->boolean('is_featured')->default(false)->after('source');
            $table->timestamp('published_at')->nullable()->after('is_featured');
            $table->softDeletes();
        });

        Schema::create('lead_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('matched_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('location_score')->nullable();
            $table->unsignedTinyInteger('plan_score')->nullable();
            $table->timestamp('matched_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['lead_id', 'agent_id']);
        });

        Schema::create('affiliate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('referral_code')->unique();
            $table->string('payout_email')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('conversion_count')->default(0);
            $table->unsignedInteger('pending_payout_cents')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('affiliate_profiles');
        Schema::dropIfExists('lead_matches');

        Schema::table('properties', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['description', 'images', 'latitude', 'longitude', 'is_featured', 'published_at']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_id');
            $table->dropConstrainedForeignId('reviewed_by_id');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'source',
                'timeline',
                'financing_status',
                'contact_preference',
                'lead_score',
                'is_priority',
                'ghl_contact_id',
                'form_data',
                'route_notes',
                'reviewed_at',
                'assigned_at',
                'contacted_at',
                'closed_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_plan_id');
            $table->dropConstrainedForeignId('referred_by_user_id');
            $table->dropColumn([
                'staff_team',
                'stripe_customer_id',
                'ghl_contact_id',
                'affiliate_code',
                'onboarding_completed_at',
                'last_synced_at',
            ]);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'billing_type',
                'stripe_price_id',
                'stripe_product_id',
                'ghl_form_url',
                'ghl_pipeline_stage',
                'is_active',
                'duration_days',
                'sort_order',
            ]);
        });
    }
};
