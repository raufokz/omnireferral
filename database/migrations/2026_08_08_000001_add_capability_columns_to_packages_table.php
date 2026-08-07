<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('portal_access')->default(false)->after('lead_priority');
            $table->boolean('property_listings')->default(false)->after('portal_access');
            $table->unsignedInteger('listing_limit')->default(0)->after('property_listings');
            $table->boolean('virtual_assistant')->default(false)->after('listing_limit');
            $table->boolean('priority_routing')->default(false)->after('virtual_assistant');
            $table->boolean('featured_placement')->default(false)->after('priority_routing');
            $table->boolean('premium_seo')->default(false)->after('featured_placement');
            $table->boolean('advanced_reporting')->default(false)->after('premium_seo');
            $table->boolean('dashboard_access')->default(false)->after('advanced_reporting');
            $table->boolean('advanced_qualification')->default(false)->after('dashboard_access');
            $table->boolean('dedicated_account_manager')->default(false)->after('advanced_qualification');
            $table->boolean('verified_referral_access')->default(false)->after('dedicated_account_manager');
            $table->unsignedInteger('referral_fee_pct')->nullable()->after('verified_referral_access');
            $table->unsignedInteger('city_limit')->default(0)->after('referral_fee_pct');
            $table->unsignedInteger('free_referrals')->default(0)->after('city_limit');
            $table->string('referral_capacity')->nullable()->after('free_referrals');
            $table->unsignedInteger('verification_steps')->default(0)->after('referral_capacity');
            $table->string('marketing_tier')->default('none')->after('verification_steps');
            $table->string('profile_tier')->default('none')->after('marketing_tier');
            $table->string('support_tier')->default('none')->after('profile_tier');
            $table->string('analytics_level')->default('none')->after('support_tier');
            $table->json('services')->nullable()->after('analytics_level');
        });

        // Backfill from the previously-hardcoded PlanCapabilities::definitions() so
        // behavior is unchanged after this migration, except Quick Lead's listing
        // access (property_listings/listing_limit) per the explicit product change.
        $backfill = [
            'starter-leads' => [
                'property_listings' => true, 'listing_limit' => 2, // was false/0 — Quick Lead product change
                'verified_referral_access' => true, 'referral_fee_pct' => 20, 'city_limit' => 2,
                'free_referrals' => 2, 'referral_capacity' => '16-20', 'verification_steps' => 1,
                'marketing_tier' => 'basic', 'profile_tier' => 'basic', 'support_tier' => 'email',
                'analytics_level' => 'basic',
            ],
            'growth-leads' => [
                'portal_access' => true, 'property_listings' => true, 'listing_limit' => 5,
                'virtual_assistant' => true, 'priority_routing' => true, 'dashboard_access' => true,
                'verified_referral_access' => true, 'referral_fee_pct' => 15, 'city_limit' => 5,
                'free_referrals' => 5, 'referral_capacity' => '30+', 'verification_steps' => 2,
                'marketing_tier' => 'better', 'profile_tier' => 'premium', 'support_tier' => 'email_sms',
                'analytics_level' => 'enhanced',
            ],
            'elite-leads' => [
                'portal_access' => true, 'property_listings' => true, 'listing_limit' => 10,
                'virtual_assistant' => true, 'priority_routing' => true, 'featured_placement' => true,
                'premium_seo' => true, 'advanced_reporting' => true, 'dashboard_access' => true,
                'advanced_qualification' => true, 'dedicated_account_manager' => true,
                'verified_referral_access' => true, 'referral_fee_pct' => 10, 'city_limit' => 10,
                'free_referrals' => 9, 'referral_capacity' => '50+', 'verification_steps' => 3,
                'marketing_tier' => 'premium', 'profile_tier' => 'premium', 'support_tier' => 'priority',
                'analytics_level' => 'advanced',
            ],
            'cold-calling-isa' => [
                'support_tier' => 'priority',
                'services' => json_encode(['Dedicated ISA', 'Appointment Setting', 'Lead Follow-up', 'CRM Updates', 'KPI Reporting', 'Territory Management']),
            ],
            'social-media-mgmt' => [
                'support_tier' => 'priority',
                'services' => json_encode(['Daily Content', 'Reels', 'Shorts', 'Facebook', 'Instagram', 'LinkedIn', 'TikTok', 'Brand Strategy', 'Monthly Review']),
            ],
            'individual-va' => [
                'support_tier' => 'email',
                'services' => json_encode(['CRM Support', 'Scheduling', 'Email Management', 'Data Entry', 'Administrative Tasks', 'WordPress Support', 'Shopify Support']),
            ],
        ];

        foreach ($backfill as $slug => $values) {
            DB::table('packages')->where('slug', $slug)->update($values);
        }

        // Add the missing "listings/month" bullet to Quick Lead's public feature
        // list without disturbing its existing marketing copy.
        $starter = DB::table('packages')->where('slug', 'starter-leads')->first();
        if ($starter) {
            $features = json_decode((string) $starter->features, true) ?: [];
            $hasListingBullet = collect($features)->contains(fn ($f) => str_contains(strtolower((string) $f), 'active property listing'));
            if (! $hasListingBullet) {
                array_splice($features, 1, 0, ['Up to 2 Active Property Listings / Month']);
                DB::table('packages')->where('slug', 'starter-leads')->update(['features' => json_encode($features)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'portal_access', 'property_listings', 'listing_limit', 'virtual_assistant',
                'priority_routing', 'featured_placement', 'premium_seo', 'advanced_reporting',
                'dashboard_access', 'advanced_qualification', 'dedicated_account_manager',
                'verified_referral_access', 'referral_fee_pct', 'city_limit', 'free_referrals',
                'referral_capacity', 'verification_steps', 'marketing_tier', 'profile_tier',
                'support_tier', 'analytics_level', 'services',
            ]);
        });
    }
};
