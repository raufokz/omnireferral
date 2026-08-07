<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentStatusSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function completeProfileAttributes(): array
    {
        return [
            'brokerage_name' => 'Test Brokerage',
            'license_number' => 'TX-999999',
            'bio' => 'Experienced local agent.',
            'specialties' => 'Buyer Representation',
            'service_city' => 'Dallas',
            'headshot' => 'assets/images/default-agent-avatar.svg',
            'profile_status' => RealtorProfile::STATUS_APPROVED,
            'approved_at' => now(),
        ];
    }

    public function test_all_four_conditions_true_yields_active_agent(): void
    {
        $package = Package::factory()->create(['category' => 'lead']);
        $agent = User::factory()->create(['role' => 'agent', 'status' => 'active', 'current_plan_id' => $package->id]);
        $profile = RealtorProfile::where('user_id', $agent->id)->first();
        $profile->update($this->completeProfileAttributes());

        $summary = $profile->fresh(['user'])->agentStatusSummary();

        $this->assertTrue($summary['approved']);
        $this->assertTrue($summary['package_purchased']);
        $this->assertTrue($summary['subscription_active']);
        $this->assertTrue($summary['profile_completed']);
        $this->assertSame(100, $summary['profile_completion_pct']);
        $this->assertTrue($summary['is_active_agent']);
    }

    public function test_missing_package_breaks_active_agent_status(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'status' => 'active', 'current_plan_id' => null]);
        $profile = RealtorProfile::where('user_id', $agent->id)->first();
        $profile->update($this->completeProfileAttributes());

        $summary = $profile->fresh(['user'])->agentStatusSummary();

        $this->assertFalse($summary['package_purchased']);
        $this->assertFalse($summary['subscription_active']);
        $this->assertFalse($summary['is_active_agent']);
        $this->assertTrue($summary['approved']);
        $this->assertTrue($summary['profile_completed']);
    }

    public function test_unapproved_profile_breaks_active_agent_status(): void
    {
        $package = Package::factory()->create(['category' => 'lead']);
        $agent = User::factory()->create(['role' => 'agent', 'status' => 'active', 'current_plan_id' => $package->id]);
        $profile = RealtorProfile::where('user_id', $agent->id)->first();
        $profile->update([...$this->completeProfileAttributes(), 'profile_status' => RealtorProfile::STATUS_DRAFT, 'approved_at' => null]);

        $summary = $profile->fresh(['user'])->agentStatusSummary();

        $this->assertFalse($summary['approved']);
        $this->assertFalse($summary['is_active_agent']);
        $this->assertTrue($summary['package_purchased']);
    }

    public function test_incomplete_profile_breaks_active_agent_status_but_not_login(): void
    {
        $package = Package::factory()->create(['category' => 'lead']);
        $agent = User::factory()->create(['role' => 'agent', 'status' => 'active', 'current_plan_id' => $package->id]);
        $profile = RealtorProfile::where('user_id', $agent->id)->first();
        $profile->update([
            'profile_status' => RealtorProfile::STATUS_APPROVED,
            'approved_at' => now(),
            // brokerage/license/bio/specialty/city/headshot left blank
        ]);

        $summary = $profile->fresh(['user'])->agentStatusSummary();

        $this->assertFalse($summary['profile_completed']);
        $this->assertLessThan(100, $summary['profile_completion_pct']);
        $this->assertFalse($summary['is_active_agent']);

        // Incomplete profile must never block dashboard access.
        $this->actingAs($agent)->get(route('dashboard.agent'))->assertOk();
    }

    public function test_listing_capacity_reflects_package_limit_and_active_usage(): void
    {
        $package = Package::factory()->create(['category' => 'lead', 'property_listings' => true, 'listing_limit' => 2]);
        $agent = User::factory()->create(['role' => 'agent', 'status' => 'active', 'current_plan_id' => $package->id]);
        $profile = RealtorProfile::where('user_id', $agent->id)->first();
        $profile->update($this->completeProfileAttributes());

        \App\Models\Property::create([
            'title' => 'Listing One', 'slug' => 'listing-one', 'status' => 'Active',
            'approval_status' => \App\Models\Property::APPROVAL_APPROVED, 'property_type' => 'house',
            'price' => 300000, 'location' => 'Dallas, TX', 'zip_code' => '75201',
            'realtor_profile_id' => $profile->id, 'owner_user_id' => $agent->id,
        ]);

        $capacity = $agent->listingCapacity($profile);

        $this->assertSame(2, $capacity['limit']);
        $this->assertSame(1, $capacity['used']);
        $this->assertSame(1, $capacity['remaining']);
        $this->assertTrue($capacity['can_create']);
    }
}
