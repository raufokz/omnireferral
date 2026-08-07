<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use App\Support\PlanCapabilities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_package_capabilities_and_they_apply_immediately(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $package = Package::factory()->create([
            'slug' => 'starter-leads',
            'category' => 'lead',
            'listing_limit' => 2,
            'portal_access' => false,
            'featured_placement' => false,
            'analytics_level' => 'basic',
        ]);

        $this->assertSame(2, PlanCapabilities::limit('starter-leads', 'listing_limit'));
        $this->assertFalse(PlanCapabilities::allows('starter-leads', 'portal_access'));

        $response = $this->actingAs($admin)->put(route('admin.packages.update', $package), [
            'name' => $package->name,
            'slug' => $package->slug,
            'category' => 'lead',
            'billing_type' => 'monthly',
            'listing_limit' => 7,
            'property_listings' => '1',
            'portal_access' => '1',
            'featured_placement' => '1',
            'analytics_level' => 'advanced',
            'support_tier' => 'priority',
            'monthly_lead_quota' => 12,
            'lead_priority' => 3,
        ]);

        $response->assertRedirect(route('admin.packages.index'));

        // No cache, no deploy — PlanCapabilities reflects the new values immediately.
        $this->assertSame(7, PlanCapabilities::limit('starter-leads', 'listing_limit'));
        $this->assertTrue(PlanCapabilities::allows('starter-leads', 'portal_access'));
        $this->assertTrue(PlanCapabilities::allows('starter-leads', 'featured_placement'));
        $this->assertSame('advanced', PlanCapabilities::for('starter-leads')['analytics_level']);

        $package->refresh();
        $this->assertSame(12, $package->monthly_lead_quota);
        $this->assertSame(3, $package->lead_priority);
    }
}
