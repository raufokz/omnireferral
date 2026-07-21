<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RealtorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_everyone_can_view_all_properties_without_404()
    {
        $user = User::factory()->create(['role' => 'agent', 'status' => 'active']);
        $profile = RealtorProfile::where('user_id', $user->id)->first();
        if (! $profile) {
            $profile = RealtorProfile::create([
                'user_id' => $user->id,
                'slug' => 'test-agent',
                'profile_status' => 'published',
            ]);
        } else {
            $profile->update(['profile_status' => 'published']);
        }

        $prop1 = Property::create([
            'realtor_profile_id' => $profile->id,
            'title' => 'Property One',
            'slug' => 'property-one',
            'location' => 'Miami FL',
            'zip_code' => '33101',
            'property_type' => 'House',
            'price' => 500000,
            'status' => 'Active',
            'approval_status' => 'approved',
        ]);

        $prop2 = Property::create([
            'realtor_profile_id' => $profile->id,
            'title' => 'Property Two New Listing',
            'slug' => 'property-two-new-listing',
            'location' => 'Tampa FL',
            'zip_code' => '33601',
            'property_type' => 'Condo',
            'price' => 350000,
            'status' => 'New Listing',
            'approval_status' => 'approved',
        ]);

        // Unauthenticated guest request to listings page
        $response = $this->get(route('listings'));
        $response->assertStatus(200);
        $response->assertSee('Property One');
        $response->assertSee('Property Two New Listing');

        // Unauthenticated guest request to detail pages
        $this->get(route('properties.show', $prop1))->assertStatus(200);
        $this->get(route('properties.show', $prop2))->assertStatus(200);
    }

    public function test_guest_and_everyone_can_view_all_realtor_profiles_without_404()
    {
        $user1 = User::factory()->create(['role' => 'agent', 'status' => 'active', 'name' => 'Agent Active']);
        $profile1 = RealtorProfile::where('user_id', $user1->id)->first();
        $profile1->update(['slug' => 'agent-active', 'profile_status' => 'published']);

        $user2 = User::factory()->create(['role' => 'agent', 'status' => 'active', 'name' => 'Agent Draft']);
        $profile2 = RealtorProfile::where('user_id', $user2->id)->first();
        $profile2->update(['slug' => 'agent-draft', 'profile_status' => 'draft']);

        // Unauthenticated guest request to agents directory
        $response = $this->get(route('agents.index'));
        $response->assertStatus(200);
        $response->assertSee('Agent Active');

        // Unauthenticated guest request to direct profile pages (no 404 for guests or users)
        $this->get(route('agents.profile', $profile1))->assertStatus(200);
        $this->get(route('agents.profile', $profile2))->assertStatus(200);
    }
}
